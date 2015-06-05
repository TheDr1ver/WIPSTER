# Functions for handling ThreatAnalyzer parsing and submissions

import sys, urllib2, json, subprocess
from poster.encode import multipart_encode
from poster.streaminghttp import register_openers
from sanalysis.settings import *

def get_ta_network(analysis_id):

    ##################################################################################
    #### Get full analysis json from each analysis ID and extract network traffic ####
    ##################################################################################
    ta_domains=[]
    ta_ips = []
    ta_commands = []
    ta_dropped = []

    extra_params = "&archive_file=Analysis/analysis.json"
    get_ta_network_res = ""

    command = "analyses/" + str(analysis_id) + "/archive_browser/get_file"
    target = ta_url + command + "?api_token=" + ta_api + extra_params

    req = urllib2.Request(target)

    try:
        response = urllib2.urlopen(req)
    except urllib2.URLError, e:
        get_ta_network_res = "Bad response code %s" % e
        return ta_ips, ta_domains, ta_commands, ta_dropped, get_ta_network_res

    httpResponse = response.getcode()
    if httpResponse == 200:
        json_result = response.read()
        try:
            result = json.loads(json_result)
        except ValueError, e:
            get_ta_network_res = "JSON load error %s" % e
            return ta_ips, ta_domains, ta_commands, ta_dropped, get_ta_network_res
        size = len(result)
        if size == 0:
            get_ta_network_res = "Response size 0"
            return ta_ips, ta_domains, ta_commands, ta_dropped, get_ta_network_res

    for k, v in result['analysis'].iteritems():
        #Grab network data from each analysis result
        if k == 'processes':
            if 'process' in k:
                for process in v['process']:

                    # Process Potential Dropped Files
                    if 'stored_files' in process:
                        if 'stored_created_file' in process['stored_files']:
                            for stored_file in process['stored_files']['stored_created_file']:
                                if '@filename' in stored_file:
                                    ta_dropped.append({'filename': stored_file['@filename'],
                                                       'md5': stored_file['@md5']})

                    # Process Observed Network Callouts
                    if 'networkpacket_section' in process:
                        if 'connect_to_computer' in process['networkpacket_section']:
                            for connection in process['networkpacket_section']['connect_to_computer']:
                                if '@remote_hostname' in connection:
                                    if connection['@remote_hostname'] not in ta_domains and connection['@remote_hostname'] not in ta_ignore_domains:
                                        ta_domains.append(connection['@remote_hostname'])
                                    if connection['@remote_ip'] not in ta_ips and connection ['@remote_ip'] not in ta_ignore_ips:
                                        ta_ips.append(connection['@remote_ip'])
                    if 'networkoperation_section' in process:
                        if 'dns_request_by_name' in process['networkoperation_section']:
                            for dns_request in process['networkoperation_section']['dns_request_by_name']:
                                if '@request_name' in dns_request:
                                    if dns_request['@request_name'] not in ta_domains and dns_request['@request_name'] not in ta_ignore_domains:
                                        ta_domains.append(dns_request['@request_name'])
                    if 'connection_section' in process:
                        if 'connection' in process['connection_section']:
                            for connection in process['connection_section']['connection']:
                                if '@remote_ip' in connection:
                                    if connection['@remote_ip'] not in ta_ips and connection['@remote_ip'] not in ta_ignore_ips:
                                        ta_ips.append(connection['@remote_ip'])
                                if '@remote_hostname' in connection:
                                    if connection['@remote_hostname'] not in ta_domains and connection['@remote_hostname'] not in ta_ignore_domains:
                                        ta_domains.append(connection['@remote_hostname'])

                                ta_single_command = ""

                                if 'http_command' in connection:
                                    for command in connection['http_command']:
                                        ta_single_command = command['@method']+": "
                                        if '@remote_hostname' in connection:
                                            ta_single_command += connection['@remote_hostname']
                                        elif '@remote_ip' in connection:
                                            ta_single_command += connection['@remote_ip']
                                        ta_single_command += command['@url']+"\r\n"
                                if 'http_header' in connection:
                                    for header in connection['http_header']:
                                        if "User-Agent" in header['@header']:
                                            ta_single_command += header['@header']+"\r\n\r\n"
                                if ta_single_command:
                                    if ta_single_command not in ta_commands:
                                        ta_commands.append(ta_single_command)

    return ta_ips, ta_domains, ta_commands, ta_dropped, get_ta_network_res

def get_ta_analyses(md5, extra_params=""):

#    extra_params = "&md5="+md5

################################################
#### Get and process /analyses/ API request ####
################################################

    get_ta_analyses_res = ""
    get_ta_risks_res = ""
    get_ta_network_res = ""
    ta_ips = []
    ta_domains = []
    ta_commands = []
    command = "analyses/"
    target = ta_url + command + "?api_token=" + ta_api + extra_params
    
    req = urllib2.Request(target)

    try:
        response = urllib2.urlopen(req)
    except urllib2.URLError, e:
        get_ta_analyses_res += "Bad response code %s \r\n" % e
        return get_ta_analyses_res, get_ta_risks_res, get_ta_network_res, ta_ips, ta_domains, ta_commands

    httpResponse = response.getcode()
    if httpResponse == 200:
        json_result = response.read()
        result = json.loads(json_result)
        size = len(result)
        if size == 0:
            get_ta_analyses_res += "Response size 0 \r\n"
            return get_ta_analyses_res, get_ta_risks_res, get_ta_network_res, ta_ips, ta_domains, ta_commands

        i = 0

        # Process response from the /analyses/ API request for 'Analyses' summary box
        for a in result['analyses']:
            '''
            get_ta_analyses_res += "Analysis number: "+str(i)+"\r\n"
            get_ta_analyses_res += "ID: " + str(a['analysis_id']) + "\r\n"
            get_ta_analyses_res += " (filename: " 
            for filename in a['filename']:
                get_ta_analyses_res += str(filename)+", "
            get_ta_analyses_res += ")\r\n"
            get_ta_analyses_res += "date: " + str(a['created_at']) + "\r\n"
            get_ta_analyses_res += "pcap: " + str(a['pcap_url']) + "\r\n"
            get_ta_analyses_res += "Risks: \r\n"
            for risk in a['risks']:
                get_ta_analyses_res += "\t" + risk['description'] + " - "
                get_ta_analyses_res += risk['maliciousness'] + "\r\n"
            get_ta_analyses_res += "Sandbox: " + a['sandbox_mac_address'] + "\r\n"
            get_ta_analyses_res += " status is " + str(a['status'])+"\r\n\r\n"
            i += 1
            '''
            get_ta_analyses_res += "ID: <a href='" + ta_base_url + "/samples/" + str(a['md5']) + "/analyses/" + str(a['analysis_id'])
            get_ta_analyses_res += "' target='_blank'>" + str(a['analysis_id']) + "</a> - "
            get_ta_analyses_res += "<a href='" + ta_base_url + "/samples/" + str(a['md5'])
            get_ta_analyses_res += "' target='_blank'>" + str(a['md5']) + "</a>" + " - "
            get_ta_analyses_res += str(a['created_at']) + " - "
            get_ta_analyses_res += str(a['status']) + " - "
            get_ta_analyses_res += "<a href='" + str(a['pcap_url']) + "'>PCAP</a>\r\n\r\n"

            # Process response from the /analyses/ API request for 'Risks' summary box
            get_ta_risks_res += "\r\n#### Analysis " + str(a['analysis_id']) + " ####\r\n"
            for risk in a['risks']:
                get_ta_risks_res += str(risk['description']) + " - " + str(risk['maliciousness']) + "\r\n"

            # Process network callouts - IPs, Domains, and HTTP Commands
            analysis_ips, analysis_domains, analysis_commands, ta_dropped, get_ta_network_res = get_ta_network(a['analysis_id'])

            for ip in analysis_ips:
                if ip not in ta_ips:
                    ta_ips.append(ip)

            for domain in analysis_domains:
                if domain not in ta_domains:
                    ta_domains.append(domain)

            for command in analysis_commands:
                if command not in ta_commands:
                    ta_commands.append(command)

    #Else - do something if response != 200

    return get_ta_analyses_res, get_ta_risks_res, get_ta_network_res, ta_ips, ta_domains, ta_commands, ta_dropped

def submit_to_ta(md5, savename, extra_params=""):

    command = "submissions/"
    target = ta_url + command + "?api_token=" + ta_api + extra_params
    savename = "sanalysis/static/"+savename

    custom_param = "custom_param[" + ta_action_name + "]"

    newname = remove_mal(savename)

    register_openers()
    if ta_group_opt != "custom":
        datagen, headers = multipart_encode({"submission[file]": open(str(newname)), 
                                             "submission[submission_type]": 'file',
                                             "submission[sandbox][group_option]": ta_group_opt,
                                             "submission[sandbox]["+ta_group_opt+"_id]": ta_group_num,
                                             "submission[priority]": ta_sub_priority,
                                             "submission[reanalyze]": ta_reanalyze,
                                             custom_param: ta_action_val })
    else:
        datagen, headers = multipart_encode({"submission[file]": open(newname),
                                             "submission[submission_type]": "file",
                                             "submission[sandbox][group_option]": ta_group_opt,
                                             "submission[sandbox][custom_sandbox][]": ta_custom_sub,
                                             "submission[priority]": ta_sub_priority,
                                             "submission[reanalyze]": ta_reanalyze,
                                             custom_param: ta_action_val })

    ta_sub_res = "Sending -XPOST " + target + "\r\n"
    ta_sub_res += "Datagen: " 
#    for k, v in datagen.iteritems():
#        ta_sub_res += str(k) + ":\t" + str(v) + "\r\n"
#    ta_sub_res += str(dir(datagen)) + "\r\n"
    ta_sub_res += str(vars(datagen)) + "\r\n"
    ta_sub_res += "Headers: " + str(headers) + "\r\n"
    req = urllib2.Request(target, datagen, headers)

    try:
        response = urllib2.urlopen(req)
    except urllib2.URLError, e:
        ta_sub_res += "Bad response code: " + str(e)
        removed_tmp = remove_tmp_file(newname) #Remove the temporarily created file with proper extension
        return ta_sub_res

    removed_tmp = remove_tmp_file(newname) #Remove the temporarily created file with proper extension
    httpResponse = response.getcode()
    if httpResponse == 200:
        json_result = response.read()
        ta_sub_res += str(json.loads(json_result))
        return ta_sub_res

def remove_mal(savename):

    newname = savename[:-4]

    run = subprocess.Popen(["cp", savename, newname],
                            stdout=subprocess.PIPE,
                            stdin=subprocess.PIPE)

    run_res = run.communicate()[0]

    return newname

def remove_tmp_file(newname):

    run = subprocess.Popen(["rm", "-f", newname],
                            stdout=subprocess.PIPE,
                            stdin=subprocess.PIPE)

    run_res = run.communicate()[0]

    return run_res
