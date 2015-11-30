# PowerManager
Free, open-source alternative to mFi Controller.

Power Manger consists of two parts. Server (server.php) and client (index.php). Server part is supposed to run persistently on the server. It writes data from outlets to database, and evaluates rules.
Client part is only for displaying status of your rules, graphs of saved data (they need to be in database first), and controlling outlets.


HOW-TO - SETUP

CONIFUGRE DEVICES

1. Install your mfi outlets, and obtain their IP addresses, log name and password.
2. Open file config/config-device.php
3. Define your devices by following provided example.

CONFIGURE DATABASE

1. Open config/config-database.php
2. Set parameters of your mysql db.
3. Set data save interval. This interval will be used to save outlet data to db. 

CONFIGURE RULES

1. Open config/config-rules.php
2. Set rules by following provided example.

CONFIGURE GUI

1. Open config/config-gui.php
2. Set name and password used to enter application.
3. Set refresh rate interval. This interval will be used to load new data and evaluate rules.
4. Set amounts of columns for each page to display outlets, graphs, and rules.

USING POWER MANAGER:

1. After entering application, the page set as default in config-gui.php is initially displayed.
2. You can switch between pages by clicking buttons OUTLETS, GRAPHS, and RULES in the bottom right.
3. You can start saving data by clicking SAVE DATA. Data will be saved in intervals specified in config-database.php (must be >0)
4. You can refresh displayed data by clicking REFRESH or waiting for number of seconds specified in config-gui.php (if 0, no auto refresh)
5. You can switch outlet by clicking appropriate box in OUTLETS page.
6. On the graph page, graphs for outlets defined in config-devices.php are displayed. You can change content of the graph by clicking appropriate button.


GENERAL USE:

You´ve got two options to use Power Manager:
1. Let it run in the browser on your server.
2. Set CRON or TASK to run php scripts ajax_scan.php (for rules evaluation), and ajax_scan.php?task=snapshot (to save data to db).



TO-DO:

1. Add total consumption option to graphs.
2. Add more info to outlets page.


COLORS MEANING:

YELLOW: Data are being updated / Data are being saved.
GREEN: Outlet is ON. Rule condition is met. Saving data to db is active.
RED: Outlet is OFF. Rule condition has not been met. Saving data to db is inactive.
