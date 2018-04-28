#include <stdio.h>
#include <unistd.h>
#include <errno.h>

int main() {
	
	char *sql = "\"string=INSERT INTO `curltest`(`IDX`, `Val`) VALUES(NULL, 39)\"";
	
	if (execl("C:/Program Files/curl/src/curl.exe", "C:/Program Files/curl/src/curl.exe", "-s", "-X", "POST", "--data-urlencode", sql, "projects.cse.tamu.edu/minkjaco/curlTest.php", (char *)NULL) == -1)
		printf("Error in execl: %d\n", errno);
	
	return 0;
}
