The 'Generic SNMP' plugin monitor allows you to monitor/test OID values against any elements within up.time(not just netSNMP or network devices).
The following fields in the plugin handle the details around the SNMP version, and appropriate credentials for the different SNMP versions. The correct choices for these fields can typically be found in the device's web admin panel or the vendor's documentation. 
	SNMP Version
	SNMP Port 
	Community String(v1/v2) 
	Agent Username (v3) 
	Authentication Type (v3) 
	Authentication Passphrase (v3) 
	Privacy Type (v3) 
	Privacy Passphrase (v3)
 
After supplying the SNMP version/credentials, we need to decide what 'SNMP action' the monitor will attempt to make against the element. This choice depends on whether we're trying to GET a single value or OID on the device (ie. SysDescr ), or looking to WALK an entire column in an SNMP Table Array ( ifSpeed column in the ifTable) and retrieve all the values. See the below Get/Walk sections for further explanation of what choice to make here, and examples for howto setup the plugin.
 
The 'SNMP Data Type' field depends on what syntax or value that your OID is expected to return, and is used by the plugin to handle whether we perform String or Integer type comparisons against the SNMP output.

Get

 Using the generic snmp plugin monitor for a 'snmp get' action will allow you pull a single OID value into up.time. Unlike the 'SNMP Poller' monitor, this OID value doesn't have to be explicitly defined within a MIB file. This allows you to monitor for 'Dynamic OIDs' that typically represent a single interface or drive on a device that isn't fully defined within the associated MIB files.
 
	SNMP Action: GET
	SNMP Data Type: String or Integer, set this based on what your OID is excepted to return.
	SNMP OID: The OID your looking to monitor


Here's an example of howto get the SysDescr OID and return that value to up.time as a string.
	SNMP Action: GET
	SNMP Data Type: String
	SNMP OID: .1.3.6.1.2.1.1.1.0
 
Walk

 The 'SNMP Walk' action allows youto pull an entire column from an SNMP array Table, and perform a threshold comparison each result in that column. 
 
	SNMP Action: Walk
	SNMP Data Type: String or Integer, set this based on what your OID is excepted to return.
	SNMP OID: The OID for the column you need to do the threshold comparison against
	SNMP Table Index OID (Walk): The OID for a column within the same Table array that can act as an index or name for the results returned.
	SNMP Table Index Filter: This field allows you to apply an optional regex filter against the SNMP Table Index, and only returns the results that match. Leave blank if you don't want to filter based on the 'SNMP Table Index OID'
 
Here's an example of howto walk the ifInErrors column of the ifTable and return the results matched with to their ifDescr:

	SNMP Action: Walk
	SNMP Data Type: Integer
	SNMP OID: .1.3.6.1.2.1.2.2.1.14
	SNMP Table Index OID (Walk): .1.3.6.1.2.1.2.2.1.2

Here's an example of howto check the ifOperStatus column in the same ifTable but only return results for those that have a ifDescr of 'vlan':

	SNMP Action: Walk
	SNMP Data Type: String
	SNMP OID: .1.3.6.1.2.1.2.2.1.8
	SNMP Table Index OID (Walk): .1.3.6.1.2.1.2.2.1.2
	SNMP Table Index Filter: vlan