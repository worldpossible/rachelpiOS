sudo mv /usr/sbin/hostapd /usr/sbin/hostapd.original 
sudo ln -sf /home/pi/hostapd_RTL /usr/sbin/hostapd
sudo chown root.root /usr/sbin/hostapd
sudo chmod 755 /usr/sbin/hostapd

sudo cp /home/pi/hostapd_realtek.conf /etc/hostapd/hostapd.conf
