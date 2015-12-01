#!/usr/bin/env python

import sys
import os
import subprocess
import argparse
import shutil
import urllib

def install_kalite():
	sudo("apt-get install -y python-pip") or die("Unable to install pip.")
	sudo("pip install ka-lite-static") or die("Unable to install KA-Lite")
	sudo("printf 'rachel\nrachel\n\n\n' | sudo kalite manage setup") or die("Unable to setup KA-Lite database.")
	return True

def install_kiwix():
	return

def check_arguments():
	sys.stdin = open('/dev/tty')
	kalite = raw_input("Would you like to install KA-Lite? [Y/n]: ").lower() or "y"
	kiwix = raw_input("Would you like to install KiwiX? [y/N]: ").lower() or "n"
	return [kalite, kiwix]

def exists(p):
	return os.path.isfile(p) or os.path.isdir(p)

def cmd(c):
	new_env = os.environ.copy()
	new_env["DEBIAN_FRONTEND"] = "noninteractive"
	result = subprocess.Popen(c, shell = True, env = new_env)
	try:
		result.communicate()
	except KeyboardInterrupt:
		pass
	return (result.returncode == 0)

def sudo(s):
	return cmd("sudo %s" % s)

def die(d):
	print d
	sys.exit(1)

def is_vagrant():
	return os.path.isfile("/etc/is_vagrant_vm")

def wifi_present():
	if is_vagrant():
		return False
	return exists("/sys/class/net/wlan0")

def basedir():
	return "/tmp/rachel_installer"
	
def cp(s, d):
	return sudo("cp %s/%s %s" % (basedir(), s, d))

[kalite, kiwix] = check_arguments()

sudo("apt-get install -y git") or die("Unable to install Git.")

# Clone the repo.
if exists("/tmp/rachel_installer"):
	sudo("rm -fr /tmp/rachel_installer")
sudo("git clone --depth 1 https://github.com/mattneel/rachelpios.git /tmp/rachel_installer") or die("Unable to clone RACHEL installer repository.")

# Chdir
os.chdir(basedir())


# Update and upgrade OS
sudo("apt-get update -y") or die("Unable to update.")
sudo("apt-get dist-upgrade -y") or die("Unable to upgrade Raspbian.")


# Update Raspi firmware
if not is_vagrant():
	sudo("rpi-update") or die("Unable to upgrade Raspberry Pi firmware")

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
	#sudo("ifdown eth0 && ifdown wlan0 && ifup eth0 && ifup wlan0") or die("Unable to restart network interfaces.")

# Setup LAN
cp("files/interfaces", "/etc/network/interfaces") or die("Unable to copy network interface configuration (interfaces)")

# Install web platform
sudo("echo mysql-server mysql-server/root_password password rachel | sudo debconf-set-selections") or die("Unable to set default MySQL password.")
sudo("echo mysql-server mysql-server/root_password_again password rachel | sudo debconf-set-selections") or die("Unable to set default MySQL password (again).")
sudo("apt-get -y install apache2 libapache2-mod-proxy-html libxml2-dev \
     php5-common libapache2-mod-php5 php5-cgi php5 \
     mysql-server mysql-client php5-mysql") or die("Unable to install web platform.")
cp("files/default", "/etc/apache2/sites-enabled/default") or die("Unable to set default Apache site.")
cp("files/my.cnf", "/etc/mysql/my.cnf") or die("Unable to copy MySQL server configuration.")
sudo("a2enmod php5 proxy proxy_html rewrite") or die("Unable to enable Apache2 dependency modules.")
sudo("service apache2 restart") or die("Unable to restart Apache2.")

# Update hostname
cp("files/hosts", "/etc/hosts") or die("Unable to copy hosts file.")
cp("files/hostname", "/etc/hostname") or die("Unable to copy hostname file.")
if not is_vagrant():
	sudo("/etc/init.d/hostname.sh") or die("Unable to set hostname.")

# Install samba share
sudo("apt-get install -y samba samba-common-bin") or die("Unable to install samba.")
sudo("mkdir -p /var/www/local") or die("Unable to create local samba share directory.")
sudo("chmod 777 /var/www/local") or die("Unable to set permissions on local samba share.")
cp("files/smb.conf", "/etc/samba/smb.conf") or die("Unable to copy samba configuration file (smb.conf).")
cp("files/gdbcommands", "/etc/samba/gdbcommands") or die("Unable to copy samba configuration file (gdbcommands).")

# Install web frontend
sudo("rm -fr /var/www/html") or die("Unable to delete existing default web application (/var/www/html).")
sudo("git clone --depth 1 https://github.com/rachelproject/contentshell /var/www/html") or die("Unable to download RACHEL web application.")
sudo("chown -R www-data.www-data /var/www/html") or die("Unable to set permissions on RACHEL web application (/var/www/html).")

# Extra wifi driver configuration
if wifi_present():
	cp("files/hostapd_RTL8188CUS", "/etc/hostapd/hostapd.conf.RTL8188CUS") or die("Unable to copy RTL8188CUS hostapd configuration.")
	cp("files/hostapd_realtek.conf", "/etc/hostapd/hostapd.conf.realtek") or die("Unable to copy realtek hostapd configuration.")

if not is_vagrant():
	if kalite == "y":
		install_kalite() or die("Unable to install KA-Lite.")
#	if kiwix == "y":
#		install_kiwix() or die("Unable to install KiwiX.")
else:
	install_kalite() or die("Unable to install KA-Lite.")
#	install_kiwix() or die("Unable to install KiwiX.")

print "RACHEL has been successfully installed. It can be accessed at: http://10.10.10.10/"