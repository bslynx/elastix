#!/usr/bin/perl -s
# Grandstream Budgetone 100 reboot & backup.
# Linux utility.

# If no ip, set ip
! $i and $i = "192.168.1.2";
# If no password, set password
! $p and $p = "admin";

# if option '-d'
my $curloption = $d ? "" : "-D -";

my $GNKEY="";
my $SESSIONKEY="";

# Load rc with $i & $p, if found
$0 =~ m:/([^/]+)$:;
my $rc = "$ENV{HOME}/.$1rc";
-e $rc and require $rc;

if ($h) {
	print "Grandstream Budgetone 100 reboot & backup.\n";
	print "© by Ole Tange and Hans Schou 2004.\n";
	print "License: GPL\n";
	print "\n";
	print "Usage:\n";
	print "\t$1 [-dh] [-i=hostip] [-p=password]\n";
	print "\n";
	print "-d\tDump data for backup.\n";
	print "-h\tThis help.\n";
	print "\n";
	print "Resource file: $rc\n";
	print "\n";
	exit(0);
}

get_gnkey();
get_cookie_setup();
if (!$d) {
	do {
		reboot();
		print "Waiting 30 seconds...";
		sleep 30;
		print "\n";
		get_gnkey();
		get_cookie_setup();
	} while($fullcone and not has_full_cone());
}

sub get_gnkey {
	open(PHONE,"curl -s $i|");
	while (<PHONE>) {
		if (/gnkey.*value=([a-fA-F0-9]+)/) {
			$GNKEY = $1;
		}
	}
	close PHONE;
	
	if (not $GNKEY) { die "Error: Can not contact '$i'" }
}

sub get_cookie_setup {
	$has_full_cone=0;
	open(PHONE,"curl $curloption -s -d \"P2=$p&gnkey=$GNKEY\" $i/dologin.htm|");
	while (<PHONE>) {
		$d and print;
		if (/Set-Cookie: (.*)/) {
			$COOKIE = $1;
		}
		/full cone/i and $has_full_cone=1;
	}
	close PHONE;
	
	if (!$d and not $COOKIE) { die "Error: Did not get cookie" }
}

sub has_full_cone {
	return $has_full_cone;
}

sub reboot {
	open(PHONE,"curl -s -b \"$COOKIE\" $i/rs.htm|");
	while (<PHONE>) {
		/reboot/i and print;
	}
	close PHONE;
}

