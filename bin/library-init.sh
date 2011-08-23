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
                                                                                                                                                                                              
# Doctrine                                                                                                                                                                                    
                                                                                                                                                                                              
if [[ -e $LIBDIR/Doctrine ]]; then                                                                                                                                                            
    echo Doctrine exists - skipping!                                                                                                                                                          
else                                                                                                                                                                                          
    svn co http://svn.doctrine-project.org/branches/1.2/lib $LIBDIR/Doctrine                                                                                                                  
fi                                                                                                                                                                                            
                                                                                                                                                                                              
# Zend                                                                                                                                                                                        
                                                                                                                                                                                              
if [[ -e $LIBDIR/Zend ]]; then                                                                                                                                                                
    echo Zend exists - skipping!                                                                                                                                                              
else                                                                                                                                                                                          
    svn co http://framework.zend.com/svn/framework/standard/branches/release-1.11/library/Zend/ $LIBDIR/Zend                                                                                  
fi                                                                                                                                                                                            
                                                                                                                                                                                              
                                                                                                                                                                                              
# Smarty                                                                                                                                                                                      
                                                                                                                                                                                              
if [[ -e $LIBDIR/Smarty ]]; then                                                                                                                                                              
    echo Smarty exists - skipping!                                                                                                                                                            
else                                                                                                                                                                                          
    svn co http://smarty-php.googlecode.com/svn/tags/Smarty_2_6_22/libs/ $LIBDIR/Smarty                                                                                                       
fi                                                                                                                                                                                            
                                                                                                                                                                                              
# ZFDebug                                                                                                                                                                                     
                                                                                                                                                                                              
if [[ -e $LIBDIR/ZFDebug ]]; then                                                                                                                                                             
    echo ZFDebug exists - skipping!                                                                                                                                                           
else                                                                                                                                                                                          
    svn co http://zfdebug.googlecode.com/svn/trunk/library/ZFDebug $LIBDIR/ZFDebug                                                                                                            
fi                                                                                                                                                                                            

