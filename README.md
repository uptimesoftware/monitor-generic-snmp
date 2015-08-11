# Monitor Generic SNMP

See http://uptimesoftware.github.io for more information.

### Tags 
 plugin   snmp   networking  

### Category

plugin

### Version Compatibility

* Generic SNMP 1.3 - 7.3
* Generic SNMP 1.2 - 7.3  
* Generic SNMP 1.0 - 7.2
  


### Description
This plug-in allows user to monitor any SNMP OID without having to add the MIB's to up.time. It also allows users to include filters so only certain data is included/excluded.


### Improvements in v1.3 of Generic SNMP Monitor

* PLUG-87 - Monitor will now return an UNKNOWN status if it's unable to retrive an OID due to timeout/connections on on the SNMP WALK/GET calls.
* PLUG-100 - Fixed an issue where integers returned from a SNMP Get where not being properly retained for Graphing. As well situations where a valid 0 response from a SNMP Get would be returned as a CRIT.
* PLUG-101 - Improved how the result of an SNMP Walk is matched to the SNMP Index Column



### Supported Monitoring Stations

* 7.3, 7.2

### Supported Agents
None; no agent required

### Installation Notes
<p><a href="https://github.com/uptimesoftware/uptime-plugin-manager">Install using the up.time Plugin Manager</a></p>
<p><a href="http://docs.uptimesoftware.com/display/KB/Working+with+the+Generic+SNMP+plugin+monitor">Working with the Generic SNMP plugin monitor</a></p>


### Dependencies
<p>n/a</p>


### Input Variables

* SNMP Version - SNMP version (1/2/3)

* SNMP Port - the port SNMP is listening on

* SNMP Action - SNMP Walk/Get

* SNMP Data Type - integer/string

* SNMP OID - the SNMP OID to get. This is used for both SNMP Walk and Get.

* SNMP Table Index OID (Walk) - specify the SNMP OID to use for the index of SNMP Walk data

* SNMP Table Index Filter - if there is specific index that one wants to include, provide regex meeting the criteria.

* Community String(v1/v2) - SNMP Community String for SNMP V1 or V2

* Agent Username (v3)

* Authentication Type (v3)

* Authentication Passphrase (v3)

* Privacy Type (v3)

* Privacy Passphrase (v3)


### Output Variables


* Returned Data (Integer)

* Returned Data (String)


### Languages Used

* PHP

