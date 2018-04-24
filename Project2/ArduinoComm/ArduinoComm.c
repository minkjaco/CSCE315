#include <pthread.h>
#include "Include/arduino-serial-lib.h"
#include "Include/Connector/include/mysql.h"

#define		NUM_LOCATIONS		1
#define		MAX_LOCATIONS		7

#define 	SINGLE_DIR			0
#define		DOUBLE_DIR			1

#define		START				1

#define		TRIG_DIST			400
#define		RATE				9600

#define		MAX_BUF_SIZE		20		


/* SerialThreadArgs
 * port: /dev/tty/xxx is the name of the usb serial port
 * fd: reference to the serial port by integer
 * loc: location of the Arduino by integer (0 - 9 inclusive)
 * type: type data recording required for this location
 * db: reference to database for pushing
 * m: mutex to protect access to the db object
 */
struct SerialThreadArgs {
	char *port;
	int fd;
	int loc;
	int type;
	MYSQL *db;
	pthread_mutex_t *m;
}

/* void *serialThread (void *args)
 * Function that handles data collection from an Arduino
 * and pushes to the MySQL database
 */ 
void *serialThread(void *args) {
	struct SerialThreadArgs *sta = (struct SerialThreadArgs *)args;
	char *buf = calloc(MAX_BUF_SIZE + 1, 1);
	int res = 0;
	char *query = calloc(100 + 1, 1);
	
	// Cars exit in one location and enter in another
	// Simple case
	if (sta->type == SINGLE_DIR) {
		bool readA = true;
		bool readB = true;
		while (true) {
			res = serialport_read_until(sta->fd, buf, '\n', MAX_BUF_SIZE, 200);
			if (res == -1)
				printf("Read to port %s, fd %d failed\n", sta->port, sta->fd);
			else if (res == -2)
				printf("Read timeout on port %s, fd %d\n", sta->port, sta->fd);
			
			float val = atof(buf + 1);
			if (!readA && buf[0] == 'A' && val >= TRIG_DIST)
				readA = true;
			else if (!readB && buf[0] == 'B' && val >= TRIG_DIST)
				readB = true;
			
			if (readA && val < TRIG_DIST) {
				sprintf(query, "INSERT INTO %s VALUES(NULL, %d, true, false, CURRENT_TIMESTAMP)", database, sta->loc);
				pthread_mutex_lock(sta->m);
				if (mysql_query(sta->db, query) != 0) {
					printf("Query failed from %d\n", sta->fd);
				}
				pthread_mutex_unlock(sta->m);
				readA = false;
			}
			else if (readB && val < TRIG_DIST) {
				sprintf(query, "INSERT INTO %s VALUES(NULL, %d, false, true, CURRENT_TIMESTAMP)", database, sta->loc);
				pthread_mutex_lock(sta->m);
				if (mysql_query(sta->db, query) != 0) {
					printf("Query failed from %d\n", sta->fd);
				}
				pthread_mutex_unlock(sta->m);
				readB = false;
			}
		}
	}
	// Cars exit and enter on the same road
	// More difficult case, use sequential sensor reads
	else if (sta->type == DOUBLE_DIR) {
		while (true) {
			res = serialport_read_until(sta->fd, buf, '\n', MAX_BUF_SIZE, 200);
			if (res == -1)
				printf("Read to port %s, fd %d failed\n", sta->port, sta->fd);
			else if (res == -2)
				printf("Read timeout on port %s, fd %d\n", sta->port, sta->fd);
			
			if (buf[0] != 'A')
				continue;
			if (atoi(buf + 1) < TRIG_DIST) {
				int tries = 1000;
				while (tries) {
					res = serialport_read_until(sta->fd, buf, '\n', MAX_BUF_SIZE, 200);
					if (res == -1)
						printf("Read to port %s, fd %d failed\n", sta->port, sta->fd);
					else if (res == -2)
						printf("Read timeout on port %s, fd %d\n", sta->port, sta->fd);
					
					if (buf[0] != 'B') {
						tries--;
						continue;
					}
					if (atoi(buf + 1) < TRIG_DIST) {
						sprintf(query, "INSERT INTO %s VALUES(NULL, %d, true, false, CURRENT_TIMESTAMP)", database, sta->loc);
						pthread_mutex_lock(sta->m);
						if (mysql_query(sta->db, query) != 0) {
							printf("Query failed from %d\n", sta->fd);
						}
						pthread_mutex_unlock(sta->m);
						serialport_flush(sta->fd);
						break;
					}
					else
						tries--;
				}
			}
		}
	}
	else {
		printf("Serial %d invalid direction\n" sta->type);
		return -1
	}
}

// List of ports to which Arduinos are connected
// Must be input automatically
char *ports[] = { "/dev/tty/xxx" };

// Match type of enter/exit to the port name
int types[] = { SINGLE_DIR };

char *host = "database.cse.tamu.edu";
char *database = "minkjaco";
char *user = "minkjaco";
char *pass = "jacobmink123";

int main() {
	// Don't break the RPi
	if (NUM_LOCATIONS > MAX_LOCATIONS) {
		printf("Cannot handle more than %d locations\n", MAX_LOCATIONS);
		return -1;
	}
	
	// Set up Arduino serial ports
	int fd[NUM_LOCATIONS];
	int loc[NUM_LOCATIONS];
	char *buf = calloc(2, 1);
	int res = 0;
	
	// Handshake logic:
	// 1. Connect to serial port
	// 2. Write byte to request location
	// 3. Read location
	// 4. Flush
	for (int i = 0; i < NUM_LOCATIONS; ++i) {
		if ((fd[i] = serialport_init(ports[i], RATE)) == -1) {
			printf("Error opening port %s\n", ports[i]);
			return -1;
		}
		if (serialport_writebyte(fd[i], START) == -1) {
			printf("Error writing to serial %d\n", fd[i]);
			return -1;
		}
		res = serialport_read_until(fd[i], buf, '\n', 2, 200);
		if (res == -1) {
			printf("Error reading from serial port %d\n", fd[i]);
			return -1;
		}
		else if (res == -2) {
			printf("Serial %d read timeout\n", fd[i]);
			return -1;
		}
		loc[i] = atoi(buf);
		
		if (serialport_flush(fd[i]) == -1) {
			printf("Cannot flush serial %d\n", fd[i]);
			return -1;
	}
	cfree(buf);
	
	// Thread objects and arguments
	pthread_t *threads = (pthread_t *)calloc(NUM_LOCATIONS, sizeof(pthread_t));
	struct SerialThreadArgs *targs = (struct SerialThreadArgs *)calloc(NUM_LOCATIONS, sizeof(struct SerialThreadArgs));
	
	// Initialize and connect to MySQL table
	MYSQL *db;
	if ((db = mysql_init(db)) == NULL) {
		printf("Error initializing database object\n");
		return -1;
	}
	if ((db = mysql_real_connect(&db, host, user, pass, database, 34, NULL, 0)) == NULL) {
		printf("Error connecting to database\n");
		return -1;
	}
	
	// Shared mutex to protect database object across threads
	pthread_mutex_t m;
	pthread_mutex_init(&m, NULL);
	
	// Initialize and start all threads
	for (int i = 0; i < NUM_LOCATIONS; ++i) {
		targs->port = ports[i];
		targs->fd = fd[i];
		targs->loc = loc[i];
		targs->type = types[loc[i]];
		targs->db = &db;
		targs->m = &m;
		
		if (pthread_create(threads + i, NULL, serialThread, targs + i) != 0) {
			printf("Error creating thread %d\n", i);
			return -1;
		}
	}
	
	// Wait for all threads to quit
	for (int i = 0; i < NUM_LOCATIONS; ++i) {
		pthread_join(threads + i, NULL);
	}
	
	// Clean up
	mysql_close(&db);
	pthread_mutex_destroy(&m);
	cfree(threads);
	cfree(targs);
	
	return 0;
}







