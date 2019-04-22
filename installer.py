#!/usr/bin/env python

import sys
import os
import subprocess
import shutil
import urllib
import argparse
import fileinput

def basedir():
    path = os.path.dirname(os.path.abspath(sys.argv[0]))

    if not path:
        path = "."

    return path

def cmd(c):
    result = subprocess.Popen(c,
                              shell=True,
                              stdin=subprocess.PIPE,
                              stderr=subprocess.PIPE,
                              close_fds=True)
    try:
        result.communicate()
    except KeyboardInterrupt:
        pass
    return (result.returncode == 0)

def copy_file(src, dst):
    path = os.path.join(basedir(), src)

    if not os.path.isfile(path):
        die("Copy failed. Source " + path + "doesn't exist.")

    if not os.path.isdir(os.path.dirname(dst)):
        die("Copy failed destination folder " +
            os.path.dirname(dst) + " doesn't exist.")

    sudo("cp {0} {1}".format(path,dst))
    rachel_message("Copied {0} to {1}.".format(path, dst))

def copy_folder(src, dst):
    path = os.path.join(basedir(), src)

    if not os.path.isdir(path):
        die("Copy failed. Source folder " + path + " doesn't exist.")

    sudo("cp -Rf {0}/. {1}".format(path,dst))
    rachel_message("Copied directory {0} to {1}".format(path,dst))

def die(err):
    print "ERROR: " + err
    sys.exit(1)

def path_exists(path):
    return os.path.isfile(path) or os.path.isdir(path)

def rachel_message(msg):
    print "RACHEL: " + msg

def sudo(s):
    if not cmd("sudo DEBIAN_FRONTEND=noninteractive %s" % s):
       die(s + " command failed")

def install(s):
    sudo("apt-get -y install " + s)

def update_pi():
    rachel_message("Updating Raspberry Pi.")
    sudo("apt-get update -y")
    sudo("apt-get upgrade -y")

    if args.install_desktop:
       install("raspberrypi-ui-mods")

    if args.install_chromium:
       install("rpi-chromium-mods")

    if args.full_update:
        sudo("apt-get dist-upgrade -y")
        sudo("yes | sudo rpi-update")

    rachel_message("Successfully updated. Rebooting Now.")
    sudo("reboot now")

def fix_pid():
    rachel_message("Fixing RACHEL PID bug.")
    sudo("sh -c 'sed -i \"s/^exit 0//\" /etc/rc.local'")
    sudo("sh -c 'echo if [ ! -d /var/run/rachel/ ]\; then >> /etc/rc.local'")
    sudo("sh -c 'echo   mkdir /var/run/rachel/ >> /etc/rc.local'")
    sudo("sh -c 'echo   chown www-data:www-data /var/run/rachel/ >> /etc/rc.local'")
    sudo("sh -c 'echo fi >> /etc/rc.local'")
    sudo("sh -c 'echo exit 0 >> /etc/rc.local'")
    rachel_message("Finished fixing RACHEL PID bug.")

def install_apache():
    rachel_message("Installing Web Server.")
    php_version = "7.0"
    install("apache2")
    install("libxml2-dev")
    install("php" + php_version)
    install("php" + php_version + "-common")
    install("php" + php_version + "-cgi")
    install("php" + php_version + "-dev")
    install("php" + php_version + "-mbstring")
    install("php" + php_version + "-sqlite3")
    install("libapache2-mod-php" + php_version)
    copy_file("files/rachel/stem.so", "/usr/lib/php/20151012/stem.so")
    sudo("sh -c 'echo \'extension=stem.so\' >> /etc/php/" + php_version + "/cli/php.ini'")
    sudo("sh -c 'echo \'extension=stem.so\' >> /etc/php/" + php_version + "/apache2/php.ini'")
    sudo("sh -c 'sed -i \"s/upload_max_filesize *= *.*/upload_max_filesize = 512M/\" /etc/php/" + php_version + "/apache2/php.ini'")
    sudo("sh -c 'sed -i \"s/post_max_size *= *.*/post_max_size = 512M/\" \
    /etc/php/" + php_version + "/apache2/php.ini'")
    sudo("service apache2 stop")
    copy_file("files/apache2/apache2.conf", "/etc/apache2/apache2.conf")
    copy_file("files/apache2/contentshell.conf", "/etc/apache2/sites-available/contentshell.conf")
    sudo("a2dissite 000-default")
    sudo("a2ensite contentshell.conf")
    sudo("a2enmod php" + php_version + " proxy proxy_html rewrite")

    if path_exists("/etc/apache2/mods-available/xml2enc.load"):
        sudo("a2enmod xml2enc")

    sudo("service apache2 restart")
    rachel_message("Web Server has been successfully installed.")

def install_content():
    rachel_message("Installing content.")
    sudo("rm -rf /tmp/rachel_installer")
    sudo("rm -rf /var/www")

    if path_exists(basedir() + "/contentshell"):
        sudo("mkdir /var/www")
        copy_folder(basedir() + "/contentshell", "/var/www")
    else:
        install("git")
        sudo("git clone --depth 1 https://github.com/rachelproject/contentshell /var/www")

    sudo("chown -R www-data.www-data /var/www")
    sudo("usermod -a -G adm www-data")
    rachel_message("Content has been sucessfully installed.")

def install_hotspot():
    rachel_message("Installing Hotspot.")
    install("hostapd")
    install("dnsmasq")
    sudo("systemctl stop dnsmasq")
    sudo("systemctl stop hostapd")
    copy_file("files/networking/dhcpcd.conf", "/etc/dhcpcd.conf")
    sudo("service dhcpcd restart")
    copy_file("files/networking/dnsmasq.conf", "/etc/dnsmasq.conf")
    copy_file("files/networking/hostapd", "/etc/default/hostapd")

    if args.secure_hotspot:
        copy_file("files/networking/hostapd_secure.conf",
                  "/etc/hostapd/hostapd.conf")
    else:
        copy_file("files/networking/hostapd.conf", "/etc/hostapd/hostapd.conf")

    copy_file("files/networking/sysctl.conf", "/etc/sysctl.conf")
    sudo("sysctl -w net.ipv4.ip_forward=1")
    sudo("iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE")

    if args.share_internet:
        sudo("iptables -A FORWARD -i wlan0 -o eth0 -j ACCEPT")
        sudo("iptables -A FORWARD -i eth0 -o wlan0 -m state --state RELATED,ESTABLISHED -j ACCEPT")

    sudo("sh -c 'iptables-save > /etc/iptables.ipv4.nat'")
    sudo("sh -c 'sed -i \"s/^exit 0//\" /etc/rc.local'")
    sudo("sh -c 'echo iptables-restore < /etc/iptables.ipv4.nat >> /etc/rc.local'")
    sudo("sh -c 'echo exit 0 >> /etc/rc.local'")
    copy_file("files/networking/hosts", "/etc/hosts")
    copy_file("files/networking/hostname", "/etc/hostname")

    if args.wifi_ssid:
        wifi_ssid()

    if args.wifi_channel:
        wifi_channel()

    sudo("systemctl unmask hostapd.service")
    sudo("update-rc.d hostapd enable")
    sudo("update-rc.d dnsmasq enable")
    sudo("service hostapd start")
    sudo("service dnsmasq start")
    rachel_message("Hotspot has been sucessfully installed.")

def install_kalite():
    rachel_message("Installing KA-Lite.")
    install("python-pip")
    sudo("pip install ka-lite-static")
    setup_kalite()
    sudo("mkdir -p /etc/ka-lite")
    copy_file("files/kalite/init-functions", "/etc/default/ka-lite")
    copy_file("files/kalite/init-service", "/etc/init.d/ka-lite")
    sudo("chmod +x /etc/init.d/ka-lite")
    sudo("sh -c 'echo root >/etc/ka-lite/username'")

    if path_exists("/etc/systemd"):
        sudo("mkdir -p /etc/systemd/system/ka-lite.service.d")

    copy_file("files/kalite/init-systemd-conf", "/etc/systemd/system/ka-lite.service.d/10-extend-timeout.conf")
    sudo("update-rc.d ka-lite defaults")
    sudo("service ka-lite start")
    sudo("sh -c '/usr/local/bin/kalite --version > /etc/kalite-version'")
    rachel_message("KA-Lite has been successfully installed.")

def install_kiwix():
    rachel_message("Installing Kiwix.")
    sudo("mkdir -p /var/kiwix/bin")

    if args.kiwix_old:
        kiwix_version = "0.9"
        rachel_message("Downloading version " + kiwix_version + " of kiwix.")
        sudo("sh -c 'wget -O - http://downloads.sourceforge.net/project/kiwix/" +
             kiwix_version + "/kiwix-server-" + kiwix_version +
             "-linux-armv5tejl.tar.bz2 | tar xj -C /var/kiwix/bin'")
    else:
        kiwix_version = "1.1.0"
        url = "https://download.kiwix.org/release/kiwix-tools/kiwix-tools_linux-armhf-" + kiwix_version + ".tar.gz"
        rachel_message("Downloading version " + kiwix_version + " of kiwix.")
        sudo("sh -c 'wget -O - " + url + " | tar -xvz --strip 1 -C /var/kiwix/bin'")

    copy_file("files/kiwix/kiwix-sample.zim", "/var/kiwix/sample.zim")
    sudo("chown -R root:root /var/kiwix/bin")
    copy_file("files/kiwix/kiwix-sample-library.xml",
              "/var/kiwix/sample-library.xml")
    copy_file("files/kiwix/rachel-kiwix-start.pl",
              "/var/kiwix/bin/rachel-kiwix-start.pl")
    sudo("chmod +x /var/kiwix/bin/rachel-kiwix-start.pl")
    copy_file("files/kiwix/init-kiwix-service", "/etc/init.d/kiwix")
    sudo("chmod +x /etc/init.d/kiwix")
    sudo("update-rc.d kiwix defaults")
    sudo("service kiwix start")
    sudo("sh -c 'echo " + kiwix_version + " >/etc/kiwix-version'")
    rachel_message("Kiwix has been successfully installed.")

def install_kolibri():
    rachel_message("Installing Kolibri.")
    install("sqlite3")
    install("python-pip")
    install("libffi-dev")
    install("python3-pip")
    install("python3-pkg-resources")
    install("dirmngr")
    sudo("pip install --upgrade setuptools --user python")
    sudo("pip install cffi --upgrade")

    proc = subprocess.Popen("sudo su",
                    shell=True,
                    stdin=subprocess.PIPE,
                    stderr=subprocess.PIPE)
    proc.stdin.write("echo 'deb http://ppa.launchpad.net/learningequality/kolibri/ubuntu xenial main' > /etc/apt/sources.list.d/learningequality-ubuntu-kolibri-xenial.list")
    proc.communicate()

    sudo("apt-key adv --keyserver keyserver.ubuntu.com --recv-keys DC5BAA93F9E4AE4F0411F97C74F88ADB3194DD81")
    sudo("apt update")
    install("kolibri")
    copy_file("files/kolibri/daemon_default.conf", "/etc/kolibri/daemon.conf")
    copy_file("files/kolibri/kolibri_initd", "/etc/init.d/kolibri")

    if args.kolibri_user:
        rachel_message("Adding Kolibri user")
        sudo("adduser --quiet --disabled-password --shell /bin/bash --home /home/kolibri --gecos 'User' kolibri")
        sudo("echo 'kolibri:kolibri' | sudo chpasswd")
        sudo("sh -c 'sudo echo -n kolibri > /etc/kolibri/username'")
        copy_file("files/kolibri/daemon_kolibri.conf", "/etc/kolibri/daemon.conf")

    version = sudo("su kolibri -c 'kolibri --version'")
	
	
    proc = subprocess.Popen("sudo su pi",
                    shell=True,
                    stdin=subprocess.PIPE,
                    stderr=subprocess.PIPE)
    proc.stdin.write("sh -c 'echo kolibri --version > /etc/kolibri-version'")
    proc.communicate()

    sudo("systemctl start kolibri")
    rachel_message("Kolibri has been successfully installed.")

def setup_kalite():
    install("python-pexpect")
    rachel_message("Setting up KA-Lite.")
    import pexpect

    proc = pexpect.spawn('sudo kalite manage setup --username=' + args.ka_user +
                         ' --password=' + args.ka_pass +
                         ' --hostname=rachel --description=rachel')
    finished = False

    while finished == False:
        result = proc.expect_exact(['Press [enter] to continue...',
                              'Do you wish to continue and install it as root? (yes or no)',
                              'Keep database file and upgrade to KA Lite version 0.17.5? (yes or no)',
                              'Do you wish to download and install the content pack now? (yes or no)',
                              'Do you have a local copy of the content pack already downloaded that you want to install? (yes or no)',
                              'Do you wish to start the server now? (yes or no)',
                              pexpect.EOF,
                              pexpect.TIMEOUT],
                              timeout = 120)

        proc.logfile = sys.stdout

        if result == 0:
            proc.sendline("")
        if result == 1:
            proc.sendline("yes")
        if result == 2:
            proc.sendline("yes")
        if result == 3:
            proc.sendline("no")
        if result == 4:
            proc.sendline("no")
        if result == 5:
            proc.sendline("no")
        if result == 6:
            finished = True
            sys.stdout.flush()
            proc.interact()

            if proc.isalive():
                proc.sendline('bye')
        if result == 7:
            finished = True
            rachel_message("Setup of Ka-lite failed. Timed out waiting for " + proc.before)

def setup_rachel():
    rachel_message("Finalizing Rachel Settings.")

    if args.auto_login:
        sudo("ln -fs /etc/systemd/system/autologin@.service \
             /etc/systemd/system/getty.target.wants/getty@tty1.service",)

    if args.pi_pass:
        sudo("sh -c 'echo pi:" + args.pi_pass + " | chpasswd'")
    else:
        sudo("sh -c 'echo pi:rachel | chpasswd'")

    sudo("sh -c 'echo 04.21.2019 > /etc/rachelinstaller-version'")
    rachel_message("Rachel has been successfully installed.")
    rachel_message("Rachel can be accessed at http://rachel.pi, http://www.rachel, or 10.10.10.10")
    rachel_message("Rebooting your device")
    sudo("reboot now")

def setup_sudoers():
    rachel_message("Setting up permissions.")
    copy_file("files/rachel/rachel_sudoers", "/etc/sudoers.d/rachel")
    sudo("chown root:root /etc/sudoers.d/rachel")
    sudo("chmod 0440 /etc/sudoers.d/rachel")
    rachel_message("Successfully set up permissions.")

def wifi_channel():
    rachel_message("Setting Wifi Channel")

    if args.wifi_channel < 0:
        die("Invalid wifi channel provided.")

    if args.wifi_channel > 11:
        die("Invalid wifi channel provided.")

    sudo("sh -c 'sed -i \"s/channel *= *.*/channel=" + str(args.wifi_channel) + "/\" /etc/hostapd/hostapd.conf'")
    rachel_message("Successfully set wifi channel")

def wifi_ssid():
    rachel_message("Setting wifi SSID.")

    if len(args.wifi_ssid) > 32:
        rachel_message("Can not set custom SSID. The provided SSID is too long.")
    else:
        sudo("sh -c 'sed -i \"s/ssid *= *.*/ssid=" + args.wifi_ssid + "/\" /etc/hostapd/hostapd.conf'")
        rachel_message("Successfully set WiFi SSID.")

def parse_args():
    rachel_message("Parsing command line arguments")
    global args
    parser  = argparse.ArgumentParser()
    pi_args = parser.add_argument_group(description='Pi Options')
    pi_args.add_argument('--update-pi',
                         action='store_true',
                         help='Update the Raspberry Pi. Requires a reboot.',
                         dest='update_pi')
    pi_args.add_argument('--full-update',
                         action='store_true',
                         dest='full_update',
                         help='Update the pi kernel and dist during update.')
    pi_args.add_argument('--chromium',
                         action='store_true',
                         dest='install_chromium',
                         help='Install chromium during update.')
    pi_args.add_argument('--desktop',
                         action='store_true',
                         dest='install_desktop',
                         help='Install desktop during update.')
    pi_args.add_argument('--auto-login',
                         action='store_true',
                         help='Do not prompt for a password at boot.',
                         dest='auto_login')
    pi_args.add_argument('--pi-pass',
                         action='store',
                         help='Set a custom password for the pi.',
                         dest='pi_pass')
    ka_args = parser.add_argument_group(description='KA-Lite Options')
    ka_args.add_argument('--no-ka-lite',
                         action='store_true',
                         dest='no_kalite',
                         help='Disable Ka-lite installation.')
    ka_args.add_argument('--ka-lite-pass',
                         action='store',
                         dest='ka_pass',
                         default='rachel',
                         help='Password for ka-lite')
    ka_args.add_argument('--ka-lite-user',
                         action='store',
                         dest='ka_user',
                         default='rachel',
                         help='User for ka-lite')
    ko_args = parser.add_argument_group(description='Kolibri Options')
    ko_args.add_argument('--no-kolibri',
                         action='store_true',
                         dest='no_kolibri',
                         help='Disable Kolibri installation.')
    ko_args.add_argument('--kolibri-user',
                         action='store_true',
                         dest='kolibri_user',
                         help='Create and use the kolibri user to run Kolibri.')    
    kw_args = parser.add_argument_group(description='Kiwix Options')
    kw_args.add_argument('--no-kiwix',
                         action='store_true',
                         dest='no_kiwix',
                         help='Disable Kiwix Installation.')
    kw_args.add_argument('--kiwix-old',
                         action='store',
                         dest='kiwix_old',
                         help='Installs version 0.9 of Kiwix.')
    net_args = parser.add_argument_group(description='Networking Options')
    net_args.add_argument('--no-hotspot',
                          action='store_true',
                          dest='no_hotspot',
                          help='Do not install the wifi hotspot.')
    net_args.add_argument('--share-internet',
                          action='store_true',
                          dest='share_internet',
                          help='Share internet to wifi clients.')
    net_args.add_argument('--secure-hotspot',
                          action='store_true',
                          dest='secure_hotspot',
                          help='Secures the hotspot with WPA2.')
    net_args.add_argument('--wifi-ssid',
                          action='store',
                          dest='wifi_ssid',
                          help='Set wifi hotspot SSID.')
    net_args.add_argument('--wifi-channel',
                          action='store',
                          type=int,
                          choices=xrange(0,11),
                          default=6,
                          dest='wifi_channel',
                          help='Set the wifi channel.')
    args = parser.parse_args()
    rachel_message("Successfully parsed command line arguments.")

def main():
    rachel_message("Beginning installation.")
    parse_args()

    if args.update_pi:
        update_pi()

    setup_sudoers()
    install_content()
    fix_pid()

    if not args.no_kalite:
        install_kalite()

    if not args.no_kiwix:
        install_kiwix()

    install_apache()

    if not args.no_kolibri:
        install_kolibri()

    if not args.no_hotspot:
        install_hotspot()

    setup_rachel()

if __name__== "__main__":
  main()
