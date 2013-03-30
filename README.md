ttrss-videoframes
=================

[TT-RSS](http://www.tt-rss.org) plugin to **enable embedded videos** in feeds.

Currently supported sites:
 * Youtube
 * Vimeo
 * Dailymotion
 * MyVideo

![Screenshot](http://i.imgur.com/MhccdQn.png)

This plugins allows the inclusion of iframes from the above listed sites without the *sandbox* attribute to allow flash videos. Additionally, the plugin will transform directly embedded flash videos from those site to iframes so they can be shown as well.

If you do not trust one or more of the sites this plugin could be considered a *security risk*. It will force the iframe to be requested over https to avoid possible MITM scenarios however.


Requires **tt-rss 1.7.5 or later**. Note, that if your browser does not support the sandbox attribute, this plugin might not work on versions of tt-rss prior to 1.7.6.

Copyright (c) 2013 [Felix Eckhofer](http://www.eckhofer.com)

>    This program is free software: you can redistribute it and/or modify
>    it under the terms of the GNU General Public License as published by
>    the Free Software Foundation, either version 3 of the License, or
>    (at your option) any later version.
>
>    This program is distributed in the hope that it will be useful,
>    but WITHOUT ANY WARRANTY; without even the implied warranty of
>    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
>    GNU General Public License for more details.
>
>    You should have received a copy of the GNU General Public License
>    along with this program.  If not, see <http://www.gnu.org/licenses/>.
