#!/bin/bash                                                                                                                                                                                   
                                                                                                                                                                                              
# Open Solutions' ViMbAdmin Project.
#
# Copyright (c) 2011 Open Source Solutions Limited
#
# ViMbAdmin is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# ViMbAdmin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                        
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with ViMbAdmin.  If not, see <http://www.gnu.org/licenses/>.
#

# This file will set up SVN externals in library/

  
# Is SVN installed and in the path?
  
svn &>/dev/null
  
if [[ $? -eq 127 ]]; then
    echo ERROR: SVN not installed or not in the path
    exit
fi
          
          
LIBDIR=`dirname "$0"`/../library                                                                                                                                                              
                                                                                                                                                                                              
for name in Doctrine Zend Smarty ZFDebug; do                                                                                                                                                  
    echo -e "\n\n\n\n\n-------------\n\nUpdating $name..."                                                                                                                                    
    cd $LIBDIR/$name                                                                                                                                                                          
    svn up                                                                                                                                                                                    
    cd -                                                                                                                                                                                      
done                                                                                                                                                                                          
