#!/bin/bash
SFTP_HOST="5019529129.ssh.w2.strato.hosting"
SFTP_USER="su600522"
SFTP_PASS="Freunde999..."

expect << EOF
set timeout 30
spawn sftp -o StrictHostKeyChecking=no ${SFTP_USER}@${SFTP_HOST}
expect "password:"
send "${SFTP_PASS}\r"
expect "sftp>"
send "put /Users/andrewbanoub/Desktop/ZuschussPiloten/admin/index.php www/admin/index.php\r"
expect "sftp>"
send "bye\r"
expect eof
EOF
echo "Fertig! Öffne: https://zuschusspiloten.de/admin/?debug"
