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


echo "Tga your version :"
echo "svn copy -m'release $RELNUM' http://forge.lulu.com/svn/lulubot/trunk/ http://forge.lulu.com/svn/lulubot/tags/release-$RELNUM"

cd
if [ ! -d $BUILDS ]; then
	mkdir $BUILDS
fi

cd $BUILDS
echo -n "Exporting ... "
svn export http://forge.lulu.com/svn/lulubot/tags/release-$RELNUM lulubot-$RELNUM
echo "ok."

cd lulubot-$RELNUM
echo -n "Expanding macros ... "
grep -lr '\$Header\$' * | xargs -- perl -pi -e "s/\\\$Header\\\$/( Version $RELNUM built on $NOW by $WHO )/"
grep -lr '\$Version\$' * | xargs -- perl -pi -e "s/\\\$Version\\\$/$RELNUM/"
rm -f release.sh
echo "ok."

cd ..
echo -n "Tarballing ... "
tar cjf lulubot-$RELNUM.tar.bz2 lulubot-$RELNUM
echo "ok."

echo
echo "Done."

exit 0
