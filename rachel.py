#!/usr/bin/env python

import sys
import os
import argparse
import subprocess
import shutil


def exists(p):
	return os.path.isfile(p) or os.path.isdir(p)

def check_arguments():
	return

def cmd(c):
	new_env = os.environ.copy()
	new_env["DEBIAN_FRONTEND"] = "noninteractive"
	result = subprocess.Popen(c, shell = True, env = new_env)
	text = result.communicate()[0]
	return (result.returncode == 0)

def sudo(s):
	return cmd("sudo %s" % s)

def die(d):
	print d
	sys.exit(1)

# Update and upgrade OS
sudo("apt-get install -y git") or die("Unable to install Git.")

# Clone the repo.
if exists("/tmp/rachel_installer"):
	sudo("rm -fr /tmp/rachel_installer")
sudo("git clone https://github.com/mattneel/rachelpios.git -b installer /tmp/rachel_installer") or die("Unable to clone RACHEL installer repository.")

# Run the script
os.chdir("/tmp/rachel_installer")
sudo("python installer.py") or die("Installation failed.")