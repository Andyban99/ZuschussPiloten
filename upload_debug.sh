#!/bin/bash
# Debug-Script hochladen

SFTP_HOST="5019529129.ssh.w2.strato.hosting"
SFTP_USER="su600522"
SFTP_PASS="Freunde999..."
LOCAL_DIR="/Users/andrewbanoub/Desktop/ZuschussPiloten"

expect << EOF
set timeout 30
spawn sftp -o StrictHostKeyChecking=no ${SFTP_USER}@${SFTP_HOST}
expect "password:"
send "${SFTP_PASS}\r"
expect "sftp>"
send "lcd ${LOCAL_DIR}\r"
expect "sftp>"
send "put admin/debug.php www/admin/debug.php\r"
expect "sftp>"
send "bye\r"
expect eof
EOF

echo "Debug-Script hochgeladen! Öffne: https://zuschusspiloten.de/admin/debug.php"
