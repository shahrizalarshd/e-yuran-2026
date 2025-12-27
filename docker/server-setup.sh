#!/bin/bash
# ============================================
# E-YURAN PPTT - DigitalOcean Server Setup
# Run this script on a fresh Ubuntu 22.04 droplet
# ============================================

set -e

echo "╔════════════════════════════════════════════╗"
echo "║   E-YURAN PPTT - Server Setup Script       ║"
echo "║   Docker + DigitalOcean Droplet            ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info() { echo -e "${GREEN}✓${NC} $1"; }
warn() { echo -e "${YELLOW}⚠${NC} $1"; }
error() { echo -e "${RED}✗${NC} $1"; exit 1; }

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    error "Please run as root: sudo bash server-setup.sh"
fi

# ==========================================
# Step 1: Update System
# ==========================================
echo ""
info "Step 1: Updating system packages..."
apt update && apt upgrade -y

# ==========================================
# Step 2: Install Docker
# ==========================================
echo ""
info "Step 2: Installing Docker..."

# Remove old versions
apt remove -y docker docker-engine docker.io containerd runc 2>/dev/null || true

# Install prerequisites
apt install -y \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# Add Docker's official GPG key
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
chmod a+r /etc/apt/keyrings/docker.gpg

# Add Docker repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Start Docker
systemctl start docker
systemctl enable docker

# Verify
docker --version
docker compose version

info "Docker installed successfully!"

# ==========================================
# Step 3: Create Deployer User
# ==========================================
echo ""
info "Step 3: Creating deployer user..."

if id "deployer" &>/dev/null; then
    warn "User 'deployer' already exists"
else
    adduser --disabled-password --gecos "" deployer
    usermod -aG docker deployer
    usermod -aG sudo deployer
    
    # Setup SSH for deployer
    mkdir -p /home/deployer/.ssh
    cp /root/.ssh/authorized_keys /home/deployer/.ssh/
    chown -R deployer:deployer /home/deployer/.ssh
    chmod 700 /home/deployer/.ssh
    chmod 600 /home/deployer/.ssh/authorized_keys
    
    info "User 'deployer' created and added to docker group"
fi

# ==========================================
# Step 4: Create Project Directory
# ==========================================
echo ""
info "Step 4: Creating project directory..."

mkdir -p /opt/eyuran
chown deployer:deployer /opt/eyuran

# ==========================================
# Step 5: Setup Firewall
# ==========================================
echo ""
info "Step 5: Configuring firewall..."

ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

info "Firewall configured!"

# ==========================================
# Step 6: Install Additional Tools
# ==========================================
echo ""
info "Step 6: Installing additional tools..."

apt install -y \
    htop \
    vim \
    git \
    unzip \
    fail2ban

# Start fail2ban
systemctl start fail2ban
systemctl enable fail2ban

# ==========================================
# Step 7: Setup Log Rotation
# ==========================================
echo ""
info "Step 7: Configuring log rotation..."

cat > /etc/logrotate.d/eyuran << 'EOF'
/opt/eyuran/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 deployer deployer
}
EOF

# ==========================================
# Step 8: Create Docker Network
# ==========================================
echo ""
info "Step 8: Creating Docker network..."

docker network create eyuran-network 2>/dev/null || warn "Network already exists"

# ==========================================
# Summary
# ==========================================
echo ""
echo "╔════════════════════════════════════════════╗"
echo "║   ✅ SERVER SETUP COMPLETED!               ║"
echo "╚════════════════════════════════════════════╝"
echo ""
echo "Next steps:"
echo "1. SSH as deployer: ssh deployer@YOUR_IP"
echo "2. Copy docker-compose.prod.yml to /opt/eyuran/"
echo "3. Create .env file in /opt/eyuran/"
echo "4. Run: docker compose -f docker-compose.prod.yml up -d"
echo ""
info "Server IP: $(curl -s ifconfig.me)"
echo ""

