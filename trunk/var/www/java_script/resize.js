/* * Copyright 2013 Micah Gale
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
 */
window.onload=resize;
function resize() {
    var width = window.screen.availWidth -30;  //get the screen size
    var main=document.getElementById("main");
    var head=document.getElementById("head");
    var footer=document.getElementById("footer");
    if(width>706) {
        head.style.width=width+"px";
        if(main!==null)
            main.style.width=width+"px";
        if(footer!==null)
            footer.style.width=width+"px";
    } else {
        head.style.width=706+"px";
        if(main!==null)
            main.style.width=706+"px";
        if(footer!==null)
            footer.style.width=width+"px";
    }
}

