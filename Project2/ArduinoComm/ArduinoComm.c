#include <pthread.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <errno.h>

// Number of locations to monitor
#define		NUM_LOCATIONS		1
// Number of locations the RasberryPi can handle
#define		MAX_LOCATIONS		4

// Definitions for single-direction lanes vs. entry-exit lanes
#define 	SINGLE_DIR			0
#define		DOUBLE_DIR			1

// byte-code for handoff procedure
#define		START				0x01

// Specifies max trigger distance for the sensors
#define		TRIG_DIST			400

// Serial communication rate; must match number in .ino
#define		RATE				9600

// Basic buffer size definition
#define		MAX_BUF_SIZE		20
#define		QUERY_SIZE			200 + 1		

// List of ports to which Arduinos are connected
// Must be input automatically
char *ports[] = { "/dev/ttyACM0" };

// Match type of enter/exit to the port name
int types[] = { SINGLE_DIR };

// MySQL table information
char table[] = "traffic";
char *columns[] = { "ID", "ENTRY-EXIT", "Entering", "Exiting", "Location", "Timestamp" };

/* SerialThreadArgs
 * port: /dev/tty/xxx is the name of the usb serial port
 * ser: reference to the serial port as a file
 * loc: location of the Arduino by integer (0 - 9 inclusive)
 * type: type data recording required for this location
 * db: reference to database for pushing
 * m: mutex to protect access to the db object
 */
typedef struct SerialThreadArgs {
	char *port;
	FILE *ser;
	int loc;
	int type;
	pthread_mutex_t *m;
} SerialThreadArgs;

/* Function to read traffic in single-direction lanes
 * Preconditions: Arduino is properly connected, table definition is 
 * correctly input
 * Postconditions: None, infinite loop
*/ 
void ReadSingleDir(SerialThreadArgs *sta) {
	// initialize helper data
	char *buf = calloc(MAX_BUF_SIZE + 1, 1);
	int res = 0;
	char *query = calloc(QUERY_SIZE, 1);
	
	int readA = 1;
	int readB = 1;
	
	while (1) {
		// read serial port
		int i = 0;
		do {
			fread(buf + i, sizeof(char), 1, sta->ser);
		} while (buf[i++] != '\n');
		buf[i] = '\0';
		
		// Check if a car has left the front of the sensor after being read
		float val = atof(buf + 3);
		if (!readA && buf[0] == 'A' && val >= TRIG_DIST)
			readA = 1;
		else if (!readB && buf[0] == 'B' && val >= TRIG_DIST)
			readB = 1;
		
		// If A direction is read, send and wait for car to leave
		if (readA && val < TRIG_DIST) {
			sprintf(query, "INSERT INTO `%s`(`%s`, `%s`, `%s`, `%s`, `%s`, `%s`) VALUES(NULL, true, true, false, %d, CURRENT_TIMESTAMP)", table, columns[0], columns[1], columns[2], columns[3], columns[4], columns[5], sta->loc);
			pthread_mutex_lock(sta->m);
			if (execl("curl.exe", "curl.exe", "-s", "-X", "POST", "--data-urlencode", query, "projects.cse.tamu.edu/minkjaco/curlTest.php", (char *)NULL) == -1) {
				printf("Query failed from %d: %d\n", sta->loc, errno);
			}
			pthread_mutex_unlock(sta->m);
			readA = 0;
		}
		// If B direction is read, send and wait for car to leave
		else if (readB && val < TRIG_DIST) {
			sprintf(query, "INSERT INTO `%s`(`%s`, `%s`, `%s`, `%s`, `%s`, `%s`) VALUES(NULL, false, false, true, %d, CURRENT_TIMESTAMP)", table, columns[0], columns[1], columns[2], columns[3], columns[4], columns[5], sta->loc);
			pthread_mutex_lock(sta->m);
			if (execl("curl.exe", "curl.exe", "-s", "-X", "POST", "--data-urlencode", query, "projects.cse.tamu.edu/minkjaco/curlTest.php", (char *)NULL) == -1) {
				printf("Query failed from %d: %d\n", sta->loc, errno);
			}
			pthread_mutex_unlock(sta->m);
			readB = 0;
		}
	}
}

/* Function to read traffic entry-exit when entry and exit occur in the same lane
 * Preconditions: Arduinos are properly connected, table definition is loaded
 * Postconditions: None, infinite loop
*/
void ReadDoubleDir(SerialThreadArgs *sta) {
	// Initialize helper data
	char *buf = calloc(MAX_BUF_SIZE + 1, 1);
	char *buf2 = calloc(MAX_BUF_SIZE + 1, 1);
	int res = 0;
	char *query = calloc(QUERY_SIZE, 1);
	
	while (1) {
		// perform read on the serial port
		int i = 0;
		do {
			fread(buf + i, sizeof(char), 1, sta->ser);
		} while (buf[i++] != '\n');
		buf[i] = '\0';
		
		// Normal bi-directional sensing algorithm
		if (buf[0] != 'A')
			continue;
		if (atof(buf + 3) < TRIG_DIST) {
			int tries = 1000;
			while (tries) {
				int j = 0;
				do {
					fread(buf2 + j, sizeof(char), 1, sta->ser);
				} while (buf2[j++] != '\n');
				buf[j] = '\0';

				if (buf2[0] != 'B') {
					tries--;
					continue;
				}
				if (atof(buf2 + 3) < TRIG_DIST) {
					// car sensed in correct direction; send
					sprintf(query, "INSERT INTO `%s`(`%s`, `%s`, `%s`, `%s`, `%s`, `%s`) VALUES(NULL, true, true, false, %d, CURRENT_TIMESTAMP)", table, columns[0], columns[1], columns[2], columns[3], columns[4], columns[5], sta->loc);
					pthread_mutex_lock(sta->m);
					if (execl("curl.exe", "curl.exe", "-s", "-X", "POST", "--data-urlencode", query, "projects.cse.tamu.edu/minkjaco/curlTest.php", (char *)NULL) == -1) {
						printf("Query failed from %d: %d\n", sta->loc, errno);
					}
					pthread_mutex_unlock(sta->m);
					fflush(sta->ser);
					break;
				}
				else
					tries--;
			}
		}
	}
}

/* void *serialThread (void *args)
 * Function that handles data collection from an Arduino
 * and pushes to the MySQL database
 * Preconditions: Arduinos have been connected
 * Postconditions: each Arduino is performing a serial read in its correct style
 */ 
void *serialThread(void *args) {
	// Cars exit in one location and enter in another
	// Simple case
	SerialThreadArgs *sta = (SerialThreadArgs *)args;
	printf("Entering thread for location %d\n", sta->loc);
	if (sta->type == SINGLE_DIR) {
		printf("Single\n");
		ReadSingleDir(sta);
	}
	// Cars exit and enter on the same road
	// More difficult case, use sequential sensor reads
	else if (sta->type == DOUBLE_DIR) {
		printf("Double\n");
		ReadDoubleDir(sta);
	}
	else {
		printf("Serial %d invalid direction\n", sta->type);
		return NULL;
	}
}

/* int main()
 * Preconditions: Arduino is connected, ports are properly specified,
 * table definition is properly specified
 * Postconditions: Arduinos are connected via serial port and the serial read
 * procedures are started in separate threads
*/
int main() {
	// Don't break the RPi
	if (NUM_LOCATIONS > MAX_LOCATIONS) {
		printf("Cannot handle more than %d locations\n", MAX_LOCATIONS);
		return -1;
	}
	
	// Set up Arduino serial ports
	FILE *serial[NUM_LOCATIONS];
	int loc[NUM_LOCATIONS];
	
	// Handshake logic:
	// 1. Connect to serial port
	// 2. Write byte to request location
	// 3. Read location
	// 4. Flush
	int i;
	for (i = 0; i < NUM_LOCATIONS; ++i) {
		serial[i] = fopen(ports[i], "rb+");
		if (!serial[i]) return -1;
		printf("serial connection successful\n");
		sleep(2);

		char command = START;
		if(!fwrite(&command, sizeof(char), 1, serial[i])) return -1;
		printf("serial write value %d successful\n", START);
		
		char result;
		while (!fread(&result, sizeof(char), 1, serial[i])) printf(".");
		loc[i] = atoi(&result);
		printf("serial read successful: %d\n", loc[i]);

		fflush(serial[i]);
	}
	
	// Thread objects and arguments
	pthread_t *threads = (pthread_t *)calloc(NUM_LOCATIONS, sizeof(pthread_t));
	SerialThreadArgs *targs = (SerialThreadArgs *)calloc(NUM_LOCATIONS, sizeof(SerialThreadArgs));
	
	// Shared mutex to protect database object across threads
	pthread_mutex_t m;
	pthread_mutex_init(&m, NULL);
	
	// Initialize and start all threads
	for (i = 0; i < NUM_LOCATIONS; ++i) {
		targs[i].port = ports[i];
		targs[i].ser = serial[i];
		targs[i].loc = loc[i];
		targs[i].type = types[loc[i]];
		targs[i].m = &m;
		
		if (pthread_create(threads + i, NULL, serialThread, targs + i) != 0) {
			printf("Error creating thread %d\n", i);
			return -1;
		}
	}
	
	// Wait for all threads to quit
	for (i = 0; i < NUM_LOCATIONS; ++i) {
		pthread_join(threads[i], NULL);
	}
	
	// Clean up
	pthread_mutex_destroy(&m);
	free(threads);
	free(targs);
	
	return 0;
}







