# GitHub Actions Workflows

This folder contains CI/CD workflows for automated testing and deployment.

## Available Workflows

### 1. `tests.yml` - Automated Testing

**Triggers:**
- Pull requests to `main` or `production` branch
- Push to any branch except `main` and `production`

**What it does:**
- ✅ Runs tests on PHP 8.2 and 8.3
- ✅ Checks code coverage (minimum 80%)
- ✅ Runs code quality checks (PHPStan, PHP-CS-Fixer)
- ✅ Uploads coverage reports to Codecov

**Matrix Testing:**
- PHP 8.2
- PHP 8.3

### 2. `deploy.yml` - Automated Deployment

**Triggers:**
- Push to `main` branch
- Push to `production` branch
- Manual trigger (workflow_dispatch)

**What it does:**

1. **Testing Phase:**
   - Run all tests
   - Build assets
   - Verify database migrations

2. **Deployment Phase** (if tests pass):
   - SSH to DigitalOcean server
   - Enable maintenance mode
   - Pull latest code
   - Install dependencies
   - Build production assets
   - Run migrations
   - Clear & optimize cache
   - Restart queue workers
   - Disable maintenance mode
   - Send Telegram notification

**Requirements:**
- All tests must pass before deployment
- Only runs for `main` or `production` branch

## Required GitHub Secrets

Configure these in: **Repository Settings → Secrets and variables → Actions**

| Secret Name | Description | Example |
|------------|-------------|---------|
| `DO_HOST` | DigitalOcean droplet IP | `123.45.67.89` |
| `DO_USERNAME` | SSH username | `deployer` |
| `DO_SSH_KEY` | Private SSH key | Content of `~/.ssh/id_rsa` |
| `DO_PORT` | SSH port | `22` |
| `TELEGRAM_BOT_TOKEN` | Telegram bot token (optional) | `123456:ABC-DEF...` |
| `TELEGRAM_CHAT_ID` | Telegram chat ID (optional) | `-1001234567890` |

## How to Setup

### 1. Generate SSH Key Pair

On your local machine:

```bash
ssh-keygen -t rsa -b 4096 -C "github-actions@eyuran"
```

### 2. Add Public Key to Server

```bash
# Copy public key
cat ~/.ssh/id_rsa.pub

# SSH to server
ssh root@YOUR_DROPLET_IP

# Add to authorized_keys
mkdir -p ~/.ssh
echo "YOUR_PUBLIC_KEY" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

### 3. Add Private Key to GitHub Secrets

```bash
# Copy private key
cat ~/.ssh/id_rsa

# Go to GitHub:
# Repository → Settings → Secrets → New repository secret
# Name: DO_SSH_KEY
# Value: (paste entire private key including BEGIN/END lines)
```

### 4. Add Other Secrets

Add all required secrets as listed above.

## Usage

### Automated Deployment (Recommended)

Simply push to main branch:

```bash
git add .
git commit -m "Your changes"
git push origin main
```

GitHub Actions will automatically:
1. Run tests
2. Deploy if tests pass
3. Notify via Telegram

### Manual Deployment Trigger

1. Go to: **Actions** tab in GitHub
2. Select: **Deploy to DigitalOcean** workflow
3. Click: **Run workflow**
4. Choose branch: `main` or `production`
5. Click: **Run workflow** button

### Pull Request Testing

When you create a pull request:
- Tests will run automatically
- Code quality checks will run
- Results will show in PR status checks

## Workflow Status Badges

Add to your README.md:

```markdown
![Tests](https://github.com/YOUR_USERNAME/e-yuran-2026/workflows/Tests/badge.svg)
![Deploy](https://github.com/YOUR_USERNAME/e-yuran-2026/workflows/Deploy%20to%20DigitalOcean/badge.svg)
```

## Troubleshooting

### Deployment Fails: Permission Denied

**Problem:** SSH key not working

**Solution:**
```bash
# On server, check permissions
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys

# Check if key is added
cat ~/.ssh/authorized_keys
```

### Tests Fail in CI but Pass Locally

**Problem:** Different environment

**Solution:**
- Check PHP version in workflow matches your local
- Check dependencies are up to date
- Clear cache locally: `composer dump-autoload`

### Deployment Hangs at Composer Install

**Problem:** Server out of memory

**Solution:**
- Upgrade droplet to 2GB RAM minimum
- Or add swap space to server

### Can't Connect to Database

**Problem:** .env not configured on server

**Solution:**
```bash
ssh deployer@YOUR_SERVER_IP
cd /var/www/e-yuran
nano .env
# Configure database credentials
```

## Notifications

### Telegram Setup (Optional)

1. **Create Bot:**
   - Message @BotFather on Telegram
   - Send: `/newbot`
   - Follow instructions
   - Save the token

2. **Get Chat ID:**
   - Add bot to your group/channel
   - Send a message
   - Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
   - Find `chat.id` in response

3. **Add to GitHub Secrets:**
   - `TELEGRAM_BOT_TOKEN`: Your bot token
   - `TELEGRAM_CHAT_ID`: Your chat ID

### Success Notification

```
✅ Deployment Success!

Repository: username/e-yuran-2026
Branch: main
Commit: abc123...
Author: shah

https://eyuran.yourdomain.com
```

### Failure Notification

```
❌ Deployment Failed!

Repository: username/e-yuran-2026
Branch: main
Commit: abc123...
Author: shah

Please check GitHub Actions logs.
```

## Best Practices

1. **Always Create PR First**
   - Test changes in PR before merging to main
   - Review test results
   - Get code review

2. **Keep main Branch Stable**
   - Only merge tested code
   - main = production-ready

3. **Monitor Deployments**
   - Watch Actions tab during deployment
   - Check server logs after deployment
   - Test critical features

4. **Backup Before Major Changes**
   - Backup database
   - Backup storage files
   - Document rollback plan

5. **Use Proper Commit Messages**
   - Clear description of changes
   - Reference issue numbers
   - Example: `fix: resolve payment callback error (#42)`

## Rollback Strategy

If deployment causes issues:

```bash
# SSH to server
ssh deployer@YOUR_SERVER_IP
cd /var/www/e-yuran

# Enable maintenance mode
php artisan down

# Rollback to previous commit
git log --oneline  # Find previous commit
git reset --hard PREVIOUS_COMMIT_HASH

# Reinstall dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Rollback migrations (if needed)
php artisan migrate:rollback

# Clear cache
php artisan cache:clear
php artisan view:clear

# Disable maintenance
php artisan up
```

## Performance Optimization

### Caching in Production

The deployment workflow automatically:
- ✅ Caches config
- ✅ Caches routes
- ✅ Caches views
- ✅ Caches events

### Asset Optimization

Build assets are optimized:
- ✅ Minified CSS/JS
- ✅ Tree-shaking (removed unused code)
- ✅ Compressed images

## Security Notes

1. **Never commit secrets to Git**
   - Use GitHub Secrets
   - Use .env files on server

2. **Protect your SSH keys**
   - Use strong passwords
   - Limit key permissions (chmod 600)

3. **Use deployment user**
   - Never deploy as root
   - Use dedicated deployer user

4. **Review workflow permissions**
   - Workflows have read-only access by default
   - Only grant necessary permissions

## Further Reading

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [DigitalOcean Tutorials](https://www.digitalocean.com/community/tutorials)

---

**Happy Deploying!** 🚀



