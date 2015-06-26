# WIPSTER Settings & Variables for Sample Analysis

from django.conf import settings

#Balbuzard Settings
balbuzard_loc = getattr(settings, "balbuzard_loc", "/opt/remnux-balbuzard/balbuzard.py")

#TRiD Settings
trid_loc = getattr(settings, "trid_loc", "/opt/remnux-trid/trid")

#VirusTotal Settings
vt_use = getattr(settings, "vt_use", True) # Set False to disable VirusTotal searches, True to enable
vt_key = getattr(settings, "vt_key", "<KEY>")
#vt_short is a short list of AV's of interest to be displayed on the main Summary page
vt_short = getattr(settings, "vt_short", ['Symantec', 'Microsoft'])

#OLETools Settings
oleid_loc = getattr(settings, "oleid_loc", "/usr/lib/python2.7/dist-packages/oletools/oleid.py")
olemeta_loc = getattr(settings, "olemeta_loc", "/usr/lib/python2.7/dist-packages/oletools/olemeta.py")
olevba_loc = getattr(settings, "olevba_loc", "/usr/lib/python2.7/dist-packages/oletools/olevba.py")
rtfobj_loc = getattr(settings, "rtfobj_loc", "/usr/lib/python2.7/dist-packages/oletools/rtfobj.py")

#PDFiD Settings
pdfid_loc = getattr(settings, "pdfid_loc", "/opt/remnux-didier/pdfid.py")

#PEEPDF Settings
peepdf_loc = getattr(settings, "peepdf_loc", "/opt/remnux-peepdf/peepdf.py")

#PEFrame Settings
peframe_loc = getattr(settings, "peframe_loc", "/usr/bin/peframe")

#PEScanner Settings
pescanner_loc = getattr(settings, "pescanner_loc", "/opt/remnux-scripts/pescanner.py")

#SSDeep Comparison - Minimum threshold for recording SSDEEP results
fuzzy_threshold = getattr(settings, "fuzzy_threshold", 10)

########################
#### CRITs Settings ####
########################

crits_use = getattr(settings, "crits_use", True)
crits_page = getattr(settings, "crits_page", "https://192.168.1.110/api/v1/")
crits_base = getattr(settings, "crits_base", "https://192.168.1.110")
crits_login = getattr(settings, "crits_login", "username=<USERNAME>&api_key=<KEY>")
crits_username = getattr(settings, "crits_username", "<USERNAME>")
crits_api_key = getattr(settings, "crits_api_key", "<KEY>")

#Recommended depth is set to 3 or less. Memory usage gets sky-high if set much greater than that.
#Plus, you'll get stuff like bad.exe -> badguy.com -> bad.exe -> badguy.com -> Ticket 555, which is just dumb.
#That could be fixed later, but since we're only using a depth of 3 in-house, it's not a priority.
set_crits_depth = range(3)  #Set this to how deep you want your relationship-checks to go. Highly recommend not setting higher than 3
crits_depth = getattr(settings, "crits_depth", set_crits_depth)

#If crits_autosubmit is set to True, it will automatically submit and relate the given sample and ticket number on upload
crits_autosubmit = getattr(settings, "crits_autosubmit", True)

#crits_source = getattr(settings, "crits_source", "TESTING") #Be sure your source current exists in your CRITs instance before running
crits_source = getattr(settings, "crits_source", "WIPSTER") #Be sure your source current exists in your CRITs instance before running

#Domains, IPs, and User-Agents to ignore

crits_ignore_ips = getattr(settings, "crits_ignore_ips", ["192.168.1.120",
                                                          "192.168.1.121"])

crits_ignore_domains = getattr(settings, "crits_ignore_domains", [".*tools\.google\.com.*",
                                                                  ".*download\.windowsupdate\.com.*"])

crits_ignore_uas = getattr(settings, "crits_ignore_uas", [".*Google Update.*",
                                                          ".*Microsoft\-CryptoAPI.*"])
                                                          
crits_ignore_dropped = getattr(settings, "crits_ignore_dropped", [".*\.LNK$",
                                                                  ".*\.lnk$",
                                                                  ".*\\CryptnetUrlCache\\.*"])

#################################
#### ThreatAnalyzer Settings ####
#################################

ta_use = getattr(settings, "ta_use", True)
ta_api = getattr(settings, "ta_api", "<KEY>")
ta_url = getattr(settings, "ta_url", "http://192.168.1.200/api/v1/")
ta_base_url = getattr(settings, "ta_base_url", "http://192.168.1.200")
ta_sub_priority = getattr(settings, "ta_sub_priority", "high")
ta_group_opt = getattr(settings, "ta_group_opt", "for_all_group") # custom | for_any_group_id | for_all_group_id
#ta_group_opt = getattr(settings, "ta_group_opt", "custom") # custom | for_any_group_id | for_all_group_id
ta_group_num = getattr(settings, "ta_group_num", 4) # Only used if ta_group_opt != custom
ta_custom_sub = getattr(settings, "ta_custom_sub", "00:11:22:33:44:55") # Only used if ta_group_opt == custom
ta_action_name = getattr(settings, "ta_action_name", "ActionAfterAnalysis")
ta_action_val = getattr(settings, "ta_action_val", "revert")
ta_reanalyze = getattr(settings, "ta_reanalyze", True)

#When ta_autosubmit is set to True, all uploads will be sent to ThreatAnalyzer using the settings above
ta_autosubmit = getattr(settings, "ta_autosubmit", False)

#Domains and IPs to ignore
ta_ignore_ips = getattr(settings, "ta_ignore_ips", ['192.168.1.120', '192.168.1.121'])
ta_ignore_domains = getattr(settings, "ta_ignore_domains", [])
