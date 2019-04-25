# RACHEL-Pi OS Installer

This installer will set up and install the necessary packages and files to turn a Raspberry Pi into a RACHEL-Pi based on the Raspbian Stretch operating system.  

## Preparing a Raspbian Image

1. Download the latest Raspbian Stretch Lite from https://www.raspberrypi.org/downloads/raspbian/

2. Flash the Raspbian Stretch Lite image to your storage device using a program such as Etcher from https://www.balena.io/etcher/

3. Place the device in your Raspberry Pi and boot up 

4. Log in either locally or over ssh using the user "pi" and the password "raspberry"

5. Run the following command to automatically update your pi and reboot

```
curl -fsS https://raw.githubusercontent.com/rachelproject/rachelpiOS/raspbian_stretch/installer.py -O && python ./installer.py --update-pi
```

### Installing

Below are examples of different build configuration commands. For more options please read the "Options" section.

First change directories 

``` cd /var/tmp ```

Build a RACHEL-Pi with Kolibri, Kiwix, and internet sharing ( recommended )

```
curl -fsS https://raw.githubusercontent.com/rachelproject/rachelpiOS/raspbian_stretch/installer.py -O && python ./installer.py --share-internet --no-ka-lite --kolibri-user
```

Build a RACHEL-Pi with Kolibri, KA-Lite, Kiwix, and internet sharing

```
curl -fsS https://raw.githubusercontent.com/rachelproject/rachelpiOS/raspbian_stretch/installer.py -O && python ./installer.py --share-internet --kolibri-user
```

Build a RACHEL-Pi with KA-Lite and internet sharing   

```
curl -fsS https://raw.githubusercontent.com/rachelproject/rachelpiOS/raspbian_stretch/installer.py -O && python ./installer.py --share-internet --no-kolibri
```

Build a Kiwix based RACHEL-Pi with no internet sharing ( Recommended for Pi Zero W ) 

```
curl -fsS https://raw.githubusercontent.com/rachelproject/rachelpiOS/raspbian_stretch/installer.py -O && python ./installer.py --no-kolibri --no-ka-lite
```

Build a RACHEL-Pi with no Kiwix, KA-Lite, Kolibri, or internet sharing ( Recommended for Pi Zero W ) 

```
curl -fsS https://raw.githubusercontent.com/rachelproject/rachelpiOS/raspbian_stretch/installer.py -O && python ./installer.py --no-kolibri --no-ka-lite --no-kiwix
```

Build a RACHEL-Pi with no options

```
curl -fsS https://raw.githubusercontent.com/rachelproject/rachelpiOS/raspbian_stretch/installer.py | python
```

Build a RACHEL-Pi with Kolibri, a custom WIFI SSID, and custom WIFI Channel

```
curl -fsS https://raw.githubusercontent.com/rachelproject/rachelpiOS/raspbian_stretch/installer.py -O && python ./installer.py --wifi-ssid=KOLIBRI-Pi --wifi-channel=6 --no-ka-lite --kolibri-user
```

## Options

**--wifi-ssid**
Provide an alternative WIFI SSID. For example ```--wifi-ssid=RACHEL-Pi01```

**--wifi-channel**
Provide an alternative WIFI channel. For example```--wifi-channel=6```

**--share-internet**
Share the internet connection to WIFI connected users

**--secure-hotspot**
Secure the hotspot. You can change the password in /etc/hostapd/hostapd.conf

**--no-ka-lite**
Skip the KA-Lite installation

**--no-kolibri**
Skip the Kolibri installation

**--no-kiwix**
Skip the Kiwix installation

**--no-hotspot**
Skip the hotspot installation

**--kolibri-user**
Use the Kolibri user to run Kolibri ( default is pi ) 

**--ka-lite-user**
Provide an alternative KA-Lite username. For example ```--ka-lite-user=Admin```

**--ka-lite-pass**
Provide an alternative KA-Lite password. For example ```--ka-lite-pass=MyPassword```

**--update-pi**
Run update and upgrade for apt packages and reboot 

**--full-update**
Run dist-upgrade and rpi-upate when --update-pi is used

**--auto-login**
Automatically log in at boot ( not recommended )

**--pi-pass**
Change the password for the user pi. For example ```--pi-pass=MyPassword```

**--kiwix-old**
Install version 0.9 of Kiwix instead of 1.1.0 


### Notes

* The login/pass after installation will be pi/rachelproject/rachelpiOS/raspbian_stretch/installer

* The default admin login/password will be "admin/Rachel+1"







