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

# shut down any existing server
`killall kiwix-serve 2>/dev/null`;

# remove the existing library
unlink("/var/kiwix/library.xml");

# check what rachel modules are hidden by the admin
# -- be careful not to create a db file if there isn't one,
# because then the ownership and permissions will be wrong
my %hidden;
my $db  = "/var/www/admin.sqlite";
if (-f "/var/www/admin/admin.sqlite") {
    $db = "/var/www/admin/admin.sqlite";
}
my $sql = "select moddir from modules where hidden = 1";
if (-e $db) {
    foreach my $mod (split /\n/, `/usr/bin/sqlite3 $db '$sql' 2>/dev/null`) {
	++$hidden{$mod};
    }
}

# find all the zim files in the modules directoy
# then take only the first from each module
my %zimset;
foreach my $file (`ls /var/www/modules/*/data/content/*.zim* 2>/dev/null`) {

    chomp $file;

    # skip anything that was hidden
    my ($moddir) = $file =~ /\/var\/www\/modules\/(.+?)\/data\//;
    next if $hidden{$moddir};

    my ($noext) = $file =~ /([^\/]+)\.zim.*$/;
    if (not $zimset{$noext}) {
        $zimset{$noext} = $file;
    }

}

# if there are no zim files in the modules directory
# we start up using the sample zim file (so you can at
# least tell kiwix was installed and runs)
if (not %zimset) {
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
    system($cmd) == 0 or die "Couldn't add $zim to library";
}

# try to do it right, but it seems kiwix-serve returns true even on failure :/
exit system("/var/kiwix/bin/kiwix-serve --daemon --port=81 --library /var/kiwix/library.xml");

