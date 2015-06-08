# WIPSTER Settings & Variables for Sample Analysis

from django.conf import settings

#Balbuzard Settings
balbuzard_loc = getattr(settings, "balbuzard_loc", "/opt/remnux-balbuzard/balbuzard.py")

#TRiD Settings
trid_loc = getattr(settings, "trid_loc", "/opt/remnux-trid/trid")

#VirusTotal Settings
vt_use = getattr(settings, "vt_use", False) # Set False to disable VirusTotal searches, True to enable
vt_key = getattr(settings, "vt_key", "YOUR-VIRUSTOTAL-API-KEY")
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

crits_use = getattr(settings, "crits_use", False) #Set True to use CRITs
crits_page = getattr(settings, "crits_page", "https://192.168.1.10/api/v1/")
crits_login = getattr(settings, "crits_login", "username=YOUR-USERNAME&api_key=YOUR-API-KEY")
#If crits_autosubmit is set to True, it will automatically submit and relate the given sample and ticket number on upload
crits_autosubmit = getattr(settings, "crits_autosubmit", True)

#crits_source = getattr(settings, "crits_source", "TESTING") #Be sure your source current exists in your CRITs instance before running
crits_source = getattr(settings, "crits_source", "WIPSTER") #Be sure your source current exists in your CRITs instance before running

#Domains, IPs, and User-Agents to ignore

crits_ignore_ips = getattr(settings, "crits_ignore_ips", ["192.168.1.10"])

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

ta_use = getattr(settings, "ta_use", False) #Set True to use ThreatAnalyzer
ta_api = getattr(settings, "ta_api", "THREATANALYZER-API-KEY") #ThreatAnalyzer API Key
ta_url = getattr(settings, "ta_url", "http://192.168.1.20/api/v1/") #ThreatAnalyzer location with api path
ta_base_url = getattr(settings, "ta_base_url", "http://192.168.1.20") #ThreatAnalyzer base URL
ta_sub_priority = getattr(settings, "ta_sub_priority", "high") #Priority of submissions
#ta_group_opt = getattr(settings, "ta_group_opt", "for_all_group") # custom | for_any_group_id | for_all_group_id
ta_group_opt = getattr(settings, "ta_group_opt", "custom") # custom | for_any_group_id | for_all_group_id
ta_group_num = getattr(settings, "ta_group_num", 4) # Only used if ta_group_opt != custom
ta_custom_sub = getattr(settings, "ta_custom_sub", "00:11:22:33:44:55") # Only used if ta_group_opt == custom
ta_action_name = getattr(settings, "ta_action_name", "ActionAfterAnalysis")
ta_action_val = getattr(settings, "ta_action_val", "revert")
ta_reanalyze = getattr(settings, "ta_reanalyze", True)

#When ta_autosubmit is set to True, all uploads will be sent to ThreatAnalyzer using the settings above
ta_autosubmit = getattr(settings, "ta_autosubmit", False)

#Domains and IPs to ignore
ta_ignore_ips = getattr(settings, "ta_ignore_ips", ['192.168.1.20'])
ta_ignore_domains = getattr(settings, "ta_ignore_domains", [])
