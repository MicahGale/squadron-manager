/* Copyright 2013 Micah Gale
 *
 * This file is a part of Squadron Manager
 *
 *Squadron Manager is free software licensed under the GNU General Public License version 3.
 * You may redistribute and/or modify it under the terms of the GNU General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * Squadron Manager comes without a warranty; without even the implied warranty of merchantability
 * or fitness for a particular purpose. See the GNU General Public License version 3 for more
 * details.
 *
 * You should have received the GNU General Public License version 3 with this in GPL.txt
 * if not it is available at <http://www.gnu.org/licenses/gpl.txt>.
 *
 * 
 */
function check_caps(e) {     //e is the keypress event object
//    if(e.key===e.char) {   //if the key is a character    
//    }  
      if(e.keyCode!==1) { //if character
          var kc= e.which;
          if(kc>=65&&kc<=122) {
              if((kc>=65&&kc<=90&&!e.shiftKey)||(kc>=97&&kc<=122&&e.shiftKey)) {  //if shift is reversed
                  document.getElementById("warn").innerHTML="Caps Lock is on";  //tell them the bad news
              } else {
                  document.getElementById("warn").innerHTML="";      //else make sure it's clear
              }
          }
      }
}