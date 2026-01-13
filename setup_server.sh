#!/bin/bash

SERVER_ID=$1
SERVER_DIR="servers/server_$SERVER_ID"
VERSION=${2:-"1.21.3"}
RAM=${3:-"512"}

mkdir -p "$SERVER_DIR"
cd "$SERVER_DIR" || exit 1

if [ ! -f "server.jar" ]; then
    echo "Downloading Paper $VERSION..."
    case $VERSION in
        "1.21.3")
            curl -L -o server.jar "https://api.papermc.io/v2/projects/paper/versions/1.21.3/builds/55/downloads/paper-1.21.3-55.jar"
            ;;
        "1.21.1")
            curl -L -o server.jar "https://api.papermc.io/v2/projects/paper/versions/1.21.1/builds/119/downloads/paper-1.21.1-119.jar"
            ;;
        "1.20.4")
            curl -L -o server.jar "https://api.papermc.io/v2/projects/paper/versions/1.20.4/builds/497/downloads/paper-1.20.4-497.jar"
            ;;
        "1.20.1")
            curl -L -o server.jar "https://api.papermc.io/v2/projects/paper/versions/1.20.1/builds/196/downloads/paper-1.20.1-196.jar"
            ;;
    esac
fi

echo "eula=true" > eula.txt

cat > server.properties << EOF
max-players=15
server-port=25565
online-mode=false
view-distance=4
simulation-distance=4
motd=BY MERO telegram QP4RM
enable-command-block=true
spawn-protection=0
max-world-size=5000
difficulty=normal
gamemode=survival
EOF

cat > spigot.yml << EOF
settings:
  save-user-cache-on-stop-only: true
world-settings:
  default:
    view-distance: 4
    mob-spawn-range: 4
    entity-activation-range:
      animals: 16
      monsters: 24
      raiders: 48
      misc: 8
    max-tick-time:
      tile: 20
      entity: 20
EOF

cat > paper.yml << EOF
verbose: false
settings:
  chunk-loading:
    min-load-radius: 1
    max-concurrent-sends: 1
  async-chunks:
    threads: 2
world-settings:
  default:
    max-auto-save-chunks-per-tick: 8
    optimize-explosions: true
    mob-spawner-tick-rate: 2
    game-mechanics:
      disable-chest-cat-detection: true
    max-entity-collisions: 2
    update-pathfinding-on-block-update: false
EOF

echo "Server setup complete for ID: $SERVER_ID"
echo "Version: $VERSION"
echo "RAM: ${RAM}MB"
echo "Max Players: 15"
