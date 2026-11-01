# Deadlock Game Tracker
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-11-003545?logo=mariadb&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)

A PHP services to watch a list of players matches and format them in presentable string.

Currently it includes the ability to post to a discord webhook.

<img width="1197" height="364" alt="image" src="https://github.com/user-attachments/assets/c01efa47-b95a-4afe-947d-61d341c11ffa" />

## How it works
1. Run the bin/fetchmatch.php script, use something like systemd to set it on a timer
2. It will fetch the recent matches of all tracker players from the deadlock community API
3. Any match already stored in MariaDB will be skipped
4. Gets posted to the given discord webhook

## Setup
- clone the repo
- run php composer ```composer install```
- Configure the ```.env``` before running the docker instance
- run the docker instance ```docker compose up -d```
- run the script in ./bim, manually or via a job of some sort

## Notes
- As valve does not have a direct API for the matches, the community made API is not entirely up to date. This will mean that fetching a match you just finished may not work and may never be captured. I plan on updating the project when we have a public API

