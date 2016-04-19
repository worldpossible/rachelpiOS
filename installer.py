#!/usr/bin/env python

import sys
import os
import subprocess
import argparse
import shutil
import urllib
import argparse

argparser = argparse.ArgumentParser()
argparser.add_argument( "--khan-academy",
                       choices=["none", "ka-lite"],
                       default="ka-lite",
                       help="Select Khan Academy package to install (default = \"ka-lite\")")
args = argparser.parse_args()

def install_kalite():
	sudo("apt-get install -y python-pip") or die("Unable to install pip.")
	sudo("pip install ka-lite-static") or die("Unable to install KA-Lite")
	sudo("printf '\nyes\nno\n' | sudo kalite manage setup --username=rachel --password=rachel --hostname=rachel --description=rachel")
	sudo("mkdir -p /etc/ka-lite") or die("Unable to create /etc/ka-lite configuration directory.")
	cp("files/init-functions", "/etc/default/ka-lite") or die("Unable to install KA-Lite configuration script.")
	cp("files/init-service", "/etc/init.d/ka-lite") or die("Unable to install KA-Lite service.")
	sudo("chmod +x /etc/init.d/ka-lite") or die("Unable to set permissions on KA-Lite service.")
	sudo("sh -c 'echo root >/etc/ka-lite/username'") or die("Unable to configure the userid of the KA-Lite process.")
	if exists("/etc/systemd"):
		sudo("mkdir -p /etc/systemd/system/ka-lite.service.d") or die("Unable to create KA-Lite service options directory.")
		cp("files/init-systemd-conf", "/etc/systemd/system/ka-lite.service.d/10-extend-timeout.conf") or die("Unable to increase KA-Lite service startup timeout.")
	sudo("update-rc.d ka-lite defaults") or die("Unable to register the KA-Lite service.")
	sudo("service ka-lite start") or die("Unable to start the KA-Lite service.")
	sudo("sh -c '/usr/local/bin/kalite --version > /etc/kalite-version'") or die("Unable to record kalite version")
	return True

def install_kiwix():
	sudo("mkdir -p /var/kiwix/bin") or die("Unable to make create kiwix directories")
	kiwix_version = "0.9"
	sudo("sh -c 'wget -O - http://downloads.sourceforge.net/project/kiwix/"+kiwix_version+"/kiwix-server-"+kiwix_version+"-linux-armv5tejl.tar.bz2 | tar xj -C /var/kiwix/bin'") or die("Unable to download kiwix-server")
	# the reason we have a sample zim file is so that if no modules
	# are installed you can still tell that kiwix is running
	cp("files/kiwix-sample.zim", "/var/kiwix/sample.zim") or die("Unable to install kiwix sample zim")
	cp("files/kiwix-sample-library.xml", "/var/kiwix/sample-library.xml") or die("Unable to install kiwix sample library")
	cp("files/rachel-kiwix-start.pl", "/var/kiwix/bin/rachel-kiwix-start.pl") or die("Unable to coppy rachel-kiwix-start wrapper")
	sudo("chmod +x /var/kiwix/bin/rachel-kiwix-start.pl") or die("Unable to set permissions on rachek-kiwix-start wrapper")
	cp("files/init-kiwix-service", "/etc/init.d/kiwix") or die("Unable to install kiwix service")
	sudo("chmod +x /etc/init.d/kiwix") or die("Unable to set permissions on kiwix service.")
	sudo("update-rc.d kiwix defaults") or die("Unable to register the kiwix service.")
	sudo("service kiwix start") or die("Unable to start the kiwix service.")
	sudo("sh -c 'echo "+kiwix_version+" >/etc/kiwix-version'") or die("Unable to record kiwix version.")
	return True

def exists(p):
	return os.path.isfile(p) or os.path.isdir(p)

def cmd(c):
	result = subprocess.Popen(c, shell=True, stdin=subprocess.PIPE, stderr=subprocess.PIPE, close_fds=True)
	try:
		result.communicate()
	except KeyboardInterrupt:
		pass
	return (result.returncode == 0)

def sudo(s):
	return cmd("sudo DEBIAN_FRONTEND=noninteractive %s" % s)

def die(d):
	print "Error: " + str(d)
	sys.exit(1)

def is_vagrant():
	return os.path.isfile("/etc/is_vagrant_vm")

def wifi_present():
	if is_vagrant():
		return False
	return exists("/sys/class/net/wlan0")

def basedir():
	bindir = os.path.dirname(sys.argv[0])
	if not bindir:
		bindir = "."
	if exists(bindir + "/files"):
		return bindir
	else:
		return "/tmp/rachel_installer"
	
def cp(s, d):
	return sudo("cp %s/%s %s" % (basedir(), s, d))

sudo("apt-get update -y") or die("Unable to update.")
sudo("apt-get install -y git") or die("Unable to install Git.")

# Clone the repo.
if basedir() == "/tmp/rachel_installer":
	sudo("rm -fr /tmp/rachel_installer")
	sudo("git clone --depth 1 https://github.com/rachelproject/rachelpios.git /tmp/rachel_installer") or die("Unable to clone RACHEL installer repository.")

if is_vagrant():
	sudo("mv /vagrant/sources.list /etc/apt/sources.list")
# Update and upgrade OS
sudo("apt-get update -y") or die("Unable to update.")
sudo("apt-get dist-upgrade -y") or die("Unable to upgrade Raspbian.")


# Update Raspi firmware
if not is_vagrant():
	sudo("yes | sudo rpi-update") or die("Unable to upgrade Raspberry Pi firmware")

# Setup wifi hotspot
if wifi_present():
	sudo("apt-get -y install hostapd udhcpd") or die("Unable install hostapd and udhcpd.")
	cp("files/udhcpd.conf", "/etc/udhcpd.conf") or die("Unable to copy UDHCPd configuration (udhcpd.conf)")
	cp("files/udhcpd", "/etc/default/udhcpd") or die("Unable to copy UDHCPd configuration (udhcpd)")
	cp("files/hostapd", "/etc/default/hostapd") or die("Unable to copy hostapd configuration (hostapd)")
	cp("files/hostapd.conf", "/etc/hostapd/hostapd.conf") or die("Unable to copy hostapd configuration (hostapd.conf)")
	sudo("sh -c 'echo 1 > /proc/sys/net/ipv4/ip_forward'") or die("Unable to set ipv4 forwarding")
	cp("files/sysctl.conf", "/etc/sysctl.conf") or die("Unable to copy sysctl configuration (sysctl.conf)")
	sudo("iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE") or die("Unable to set iptables MASQUERADE on eth0.")
	sudo("iptables -A FORWARD -i eth0 -o wlan0 -m state --state RELATED,ESTABLISHED -j ACCEPT") or die("Unable to forward wlan0 to eth0.")
	sudo("iptables -A FORWARD -i wlan0 -o eth0 -j ACCEPT") or die("Unable to forward wlan0 to eth0.")
	sudo("sh -c 'iptables-save > /etc/iptables.ipv4.nat'") or die("Unable to save iptables configuration.")
	sudo("ifconfig wlan0 10.10.10.10") or die("Unable to set wlan0 IP address (10.10.10.10)")
	sudo("service hostapd start") or die("Unable to start hostapd service.")
	sudo("service udhcpd start") or die("Unable to start udhcpd service.")
	sudo("update-rc.d hostapd enable") or die("Unable to enable hostapd on boot.")
	sudo("update-rc.d udhcpd enable") or die("Unable to enable UDHCPd on boot.")
	# udhcpd wasn't starting properly at boot (probably starting before interface was ready)
	# for now we we just force it to restart after setting the interface
	sudo("sh -c 'sed -i \"s/^exit 0//\" /etc/rc.local'") or die("Unable to remove exit from end of /etc/rc.local")
	sudo("sh -c 'echo ifconfig wlan0 10.10.10.10 >> /etc/rc.local; echo service udhcpd restart >> /etc/rc.local;'") or die("Unable to setup udhcpd reset at boot.")
	sudo("sh -c 'echo exit 0 >> /etc/rc.local'") or die("Unable to replace exit to end of /etc/rc.local")
	#sudo("ifdown eth0 && ifdown wlan0 && ifup eth0 && ifup wlan0") or die("Unable to restart network interfaces.")

# Setup LAN
if not is_vagrant():
	cp("files/interfaces", "/etc/network/interfaces") or die("Unable to copy network interface configuration (interfaces)")

# Install web platform
sudo("echo mysql-server mysql-server/root_password password rachel | sudo debconf-set-selections") or die("Unable to set default MySQL password.")
sudo("echo mysql-server mysql-server/root_password_again password rachel | sudo debconf-set-selections") or die("Unable to set default MySQL password (again).")
sudo("apt-get -y install apache2 libapache2-mod-proxy-html libxml2-dev \
     php5-common libapache2-mod-php5 php5-cgi php5 php5-dev php-pear \
     mysql-server mysql-client php5-mysql sqlite3 php5-sqlite") or die("Unable to install web platform.")
sudo("yes '' | sudo pecl install -f stem") or die("Unable to install php stemmer")
sudo("sh -c 'echo \'extension=stem.so\' >> /etc/php5/cli/php.ini'") or die("Unable to install stemmer CLI config")
sudo("sh -c 'echo \'extension=stem.so\' >> /etc/php5/apache2/php.ini'") or die("Unable to install stemmer Apache config")
sudo("sh -c 'sed -i \"s/upload_max_filesize *= *.*/upload_max_filesize = 512M/\" /etc/php5/apache2/php.ini'") or die("Unable to increase upload_max_filesize in apache2/php.ini")
sudo("sh -c 'sed -i \"s/post_max_size *= *.*/post_max_size = 512M/\" /etc/php5/apache2/php.ini'") or die("Unable to increase post_max_size in apache2/php.ini")
sudo("service apache2 stop") or die("Unable to stop Apache2.")
#cp("files/apache2.conf", "/etc/apache2/apache2.conf") or die("Unable to copy Apache2.conf")
cp("files/default", "/etc/apache2/sites-available/contentshell.conf") or die("Unable to set default Apache site.")
sudo("a2dissite 000-default") or die("Unable to disable default Apache site.")
sudo("a2ensite contentshell.conf") or die("Unable to enable contenthell Apache site.")
cp("files/my.cnf", "/etc/mysql/my.cnf") or die("Unable to copy MySQL server configuration.")
sudo("a2enmod php5 proxy proxy_html rewrite") or die("Unable to enable Apache2 dependency modules.")
if exists("/etc/apache2/mods-available/xml2enc.load"):
	sudo("a2enmod xml2enc") or die("Unable to enable Apache2 xml2enc module.")
sudo("service apache2 restart") or die("Unable to restart Apache2.")

# Install web frontend
sudo("rm -fr /var/www") or die("Unable to delete existing default web application (/var/www).")
sudo("git clone --depth 1 https://github.com/rachelproject/contentshell /var/www") or die("Unable to download RACHEL web application.")
sudo("chown -R www-data.www-data /var/www") or die("Unable to set permissions on RACHEL web application (/var/www).")
sudo("sh -c \"umask 0227; echo 'www-data ALL=(ALL) NOPASSWD: /sbin/shutdown' >> /etc/sudoers.d/www-shutdown\"") or die("Unable to add www-data to sudoers for web shutdown")
sudo("usermod -a -G adm www-data") or die("Unable to add www-data to adm group (so stats.php can read logs)")

# Extra wifi driver configuration
if wifi_present():
	cp("files/hostapd_RTL8188CUS", "/etc/hostapd/hostapd.conf.RTL8188CUS") or die("Unable to copy RTL8188CUS hostapd configuration.")
	cp("files/hostapd_realtek.conf", "/etc/hostapd/hostapd.conf.realtek") or die("Unable to copy realtek hostapd configuration.")

if args.khan_academy == "ka-lite":
        install_kalite() or die("Unable to install KA-Lite.")

# install the kiwix server (but not content)
install_kiwix()

# Change login password to rachel
if not is_vagrant():
	sudo("sh -c 'echo pi:rachel | chpasswd'") or die("Unable to change 'pi' password.")

# Update hostname (LAST!)
if not is_vagrant():
	cp("files/hosts", "/etc/hosts") or die("Unable to copy hosts file.")
	cp("files/hostname", "/etc/hostname") or die("Unable to copy hostname file.")
	sudo("/etc/init.d/hostname.sh") or die("Unable to set hostname.")

# record the version of the installer we're using - this must be manually
# updated when you tag a new installer
sudo("sh -c 'echo piOS-2016.04.19 > /etc/rachelinstaller-version'") or die("Unable to record rachelpiOS version.")

print "RACHEL has been successfully installed. It can be accessed at: http://10.10.10.10/"
