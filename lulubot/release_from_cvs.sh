#!/bin/sh
# 
#   Copyright (c) 2004 mose & Lulu Enterprises, Inc.
#   http://lulubot.berlios.de/
# 
#   This software is free software; you can redistribute it and/or
#   modify it under the terms of the GNU Lesser General Public
#   License as published by the Free Software Foundation; either
#   version 2.1 of the License, or (at your option) any later version.
# 
#   This software is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
#   Lesser General Public License for more details.
# 
#   You should have received a copy of the GNU Lesser General Public
#   License along with this software; if not, write to 
#   the Free Software Foundation, Inc., 
#   59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
# 

RELNUM='0.1'
BUILDS='lulubot_build'
NOW=`date +%Y-%m-%d.%H:%M`
WHO=$USER
CVSROOT=":ext:$USER@lulubot.cvs;berlios.de:/cvsroot/lulubot"
MODULE="lulubot"

if [ -z $1 ]; then
echo "Usage: tikirelease.sh <release-tag>"
	echo "  separated by dots like in 0.0.1"
	exit 0
fi

cd
if [ ! -d $BUILDS ]; then
	mkdir $BUILDS
fi

cd $BUILDS

if [ -d $VER ]; then
	rm -rf $VER
fi
mkdir $VER
cd $VER

cvs -z3 -q -d $CVSROOT co -d $MODULE-$VER -r $RELTAG $MODULE
find $MODULE-$VER -name CVS -type d | xargs -- rm -rf
find $MODULE-$VER -name .cvsignore -type f -exec rm -f {} \;
find $MODULE-$VER -type d -exec chmod 775 {} \;
find $MODULE-$VER -type f -exec chmod 664 {} \;
chmod 775 $MODULE-$VER/lulubot_go.php
rm -f $MODULE-$VER/release_*

tar -czf $MODULE-$VER.tar.gz $MODULE-$VER
tar -cjf $MODULE-$VER.tar.bz2 $MODULE-$VER
zip -r $MODULE-$VER.zip $MODULE-$VER

echo
echo "Done."

exit 0
