#!/usr/bin/perl
use strict;
use warnings;

#-------------------------------------------
# This script is used to refresh the kiwix library upon restart to
# include everything in the rachel modules directory. It is used
# as part of the kiwix init.d script
#
# Author: Jonathan Field <jfield@worldpossible.org>
# Date: 2016-03-19
#-------------------------------------------

# remove existing library
unlink("/var/kiwix/library.xml");

# find all the zim files in the modules directoy
# then take only the first from each module
my %zimset;
my $found = 0;
foreach my $file (`ls /var/www/modules/*/data/content/*.zim* 2>/dev/null`) {
    chomp $file;
    my ($noext) = $file =~ /([^\/]+)\.zim.*$/;
    if (not $zimset{$noext}) {
        $zimset{$noext} = $file;
	++$found;
    }
}

# if there are no zim files in the modules directory
# we start using the sample zim file (so you can at
# least tell kiwix was installed and runs)
if (not $found) {
    exit system("/var/kiwix/bin/kiwix-serve --daemon --port=81 --library /var/kiwix/sample-library.xml");
}

# go through the zim files and add them to the new library
foreach my $noext (keys(%zimset)) {
    my $zim = $zimset{$noext};
    my ($moddir) = $zim =~ /(\/var\/www\/modules\/.+?)\/data\//;
    my $cmd = "/var/kiwix/bin/kiwix-manage /var/kiwix/library.xml add $zim";
    if (-d "$moddir/data/index/$noext.zim.idx") {
        $cmd .= " --indexPath=$moddir/data/index/$noext.zim.idx";
    }
    #print STDERR "$cmd\n";
    system($cmd) == 0 or die "Couldn't add $zim to library";
}

# try to do it right, but it seems kiwix-serve returns true even on failure :/
exit system("/var/kiwix/bin/kiwix-serve --daemon --port=81 --library /var/kiwix/library.xml");

