#!/usr/bin/env bash

LOGFILE="./script.log"

log() {
	echo "$(date '+%d-%m-%Y %H:%M:%S') $1" | tee -a "$LOGFILE"
}

while getopts "d:" opt; do
	case $opt in
		d) dir_path="$OPTARG";;
	esac
done

if [ -z "$dir_path" ]; then
        read -p "Введите путь до корневой директории: " dir_path
fi

if [ ! -d "$dir_path" ]; then
        log "Ошибка: путь к корневой директории недоступен"
        exit 1
fi

if ! getent group dev > /dev/null; then
	groupadd dev
	log "Группа dev создана"
else
	log "Группа dev уже существует"
fi

awk -F: '$3 >= 1000 && $1 != "nobody" {print $1}' /etc/passwd | while read -r username; do
	usermod -aG dev "$username"
	log "Добавлен пользователь $username"
done

if ! grep -q '^%dev ALL=(ALL) NOPASSWD: ALL' /etc/sudoers; then
	echo '%dev ALL=(ALL) NOPASSWD: ALL' >> /etc/sudoers
	log "Группе dev выданы права sudo без пароля"
else 
	log "Группа dev уже имеет права sudo без пароля"
fi

awk -F: '$3 >= 1000 && $1 != "nobody" {print $1}' /etc/passwd | while read -r username; do
	user_group=$(id -gn "$username")
	workdir="${dir_path}/${username}_workdir"

	mkdir -p "$workdir"
	chown "$username:$user_group" "$workdir"
	chmod 660 "$workdir"


	log "Для пользователя $username создана рабочая директория $workdir. Владелец: $username. Группа: $user_group"
done
