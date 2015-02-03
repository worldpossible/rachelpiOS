sudo mv /usr/sbin/hostapd /usr/sbin/hostapd.original 
sudo ln -sf /home/pi/hostapd_RTL /usr/sbin/hostapd
sudo chown root.root /usr/sbin/hostapd
sudo chmod 755 /usr/sbin/hostapd

sudo cp /home/pi/hostapd_realtek.conf /etc/hostapd/hostapd.conf

sudo ifdown wlan0
sudo service hostapd stop
sudo service udhcpd stop
sudo ifconfig wlan0 10.10.10.10
sudo service hostapd start
sudo service udhcpd start
sudo ifconfig wlan0 10.10.10.10
sudo update-rc.d hostapd enable
sudo update-rc.d udhcpd enable