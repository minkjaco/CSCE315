#include <stdio.h>
#include <unistd.h>

int main() {
	execl("curl", "curl", "-X POST -F 'string='INSERT INTO `test` VALUES(1)' -F 'press=1' webprojects.cse.tamu.edu/minkjaco/curlTest.php", (char *)NULL);
	printf("done\n");
}
