#### Define Vars ####
from django.core.validators import URLValidator
from django.core.exceptions import ValidationError
from sanalysis.settings import *

from poster.encode import multipart_encode
from poster.streaminghttp import register_openers

import re, urllib, urllib2, json, time, threatanalyzer

crits_dict_main = {}
crits_dict_main['crits_ips'] = []
crits_dict_main['crits_domains'] = []
crits_dict_main['crits_uas'] = []
crits_dict_main['crits_vts'] = []

def search_ignore_list(item, ignore_list):
    #Search ignore list for a regex match of the keyword in question
    #Returns True if a match was found, False if no match was found
    match_found = False
    for pattern in ignore_list:
        is_valid = True
        search = ""
        try:
            search = re.compile(pattern)
        except re.error:
            is_valid = False
        if is_valid == True:
            if search.match(item):
                match_found = True

    return match_found


def crits_parse(ta_ips, ta_domains, ta_commands, ta_dropped):

    crits_dict_ta = {}
    crits_dict_ta.clear()
    crits_dict_ta['crits_ips'] = []
    crits_dict_ta['crits_domains'] = []
    crits_dict_ta['crits_uas'] = []
    crits_dict_ta['crits_commands'] = []
    crits_dict_ta['crits_dropped'] = []
#    crits_dict_ta['crits_vts'] = []

    for ip in ta_ips:
        if ip not in crits_dict_ta['crits_ips'] and ip not in crits_ignore_ips and ip.strip()!="":
            #If IP is not valid, don't add it
                valid = url_validate(ip)
                if valid:
                    #If IP matches regex pattern from ignore list, don't add it
                    if crits_ignore_ips: #Assuming there's an existing ignore list
                        if not search_ignore_list(ip, crits_ignore_ips):
                            crits_dict_ta['crits_ips'].append(ip)
                    else:
                        crits_dict_ta['crits_ips'].append(ip)

    for domain in ta_domains:
        if domain not in crits_dict_ta['crits_domains'] and domain not in crits_ignore_domains and domain.strip()!="":
            #If Domain is not valid, don't add it
            valid = url_validate(domain)
            if valid:
                #If Domain matches regex pattern from ignore list, don't add it
                if crits_ignore_domains: #Assuming there's an existing ignore list
                    if not search_ignore_list(domain, crits_ignore_domains):
                        crits_dict_ta['crits_domains'].append(domain)

                else:
                    crits_dict_ta['crits_domains'].append(domain)

    for command in ta_commands:
        split_command = command.split("\n")
        for line in split_command:
            if "User-Agent: " in line:
                ua = line[12:]
                if ua not in crits_dict_ta['crits_uas'] and ua not in crits_ignore_uas and ua.strip()!="":
                    #If UA matches regex pattern from ignore list, don't add it
                    if crits_ignore_uas: #Assuming there's an existing ignore list
                        if not search_ignore_list(ua, crits_ignore_uas):
                            crits_dict_ta['crits_uas'].append(ua)
                    else:                    
                        crits_dict_ta['crits_uas'].append(ua)
            else:
                line = line.strip()
                if line != "":
                    url = line.split(": ")
                    url = url[1]
                    valid = url_validate(url) #Check if command URL is valid
                    if valid:
                        if crits_ignore_domains:
                            if not search_ignore_list(url, crits_ignore_domains):
                                crits_dict_ta['crits_commands'].append(line)
                        else:
                             crits_dict_ta['crits_commands'].append(line)

    for drop in ta_dropped:
        if drop['filename'] != "" and drop['md5'] != "":    #Check to make sure the filename and md5 are populated
            if not search_ignore_list(drop['filename'], crits_ignore_dropped): #Check to make sure the filename is not on the ignore list
                if crits_dict_ta['crits_dropped']:
                    drop_check_array = []
                    for drop_check in crits_dict_ta['crits_dropped']:
                        drop_check_array.append(str(drop_check['md5']))  #Get a list of all the MD5's current in the crits_dropped dictionary
                    if str(drop['md5']) not in drop_check_array: #Make sure this MD5 is not already in the array
                        crits_dict_ta['crits_dropped'].append({'filename': str(drop['filename']),
                                                               'md5': str(drop['md5'])})
                else:
                    crits_dict_ta['crits_dropped'].append({'filename': str(drop['filename']),
                                                           'md5': str(drop['md5'])})
                
    #debug_error
    return crits_dict_ta

def crits_vt(vt_short_res):

    crits_dict_vt = {}
    crits_dict_vt.clear()
#    crits_dict_vt['crits_ip'] = []
#    crits_dict_vt['crits_domains'] = []
#    crits_dict_vt['crits_uas'] = []
    crits_dict_vt['crits_vts'] = []

    split_vt = vt_short_res.split("\n")
    for line in split_vt:
        for vendor in vt_short:
            if vendor in line and line not in crits_dict_vt['crits_vts']:
                crits_dict_vt['crits_vts'].append(line)

    return crits_dict_vt

def url_validate(url_ip):

    url_check = "http://"+url_ip
    validate = URLValidator()
    valid = True

    try:
        validate(url_check)
    except:
        valid = False

    return valid

def upload_object(data):

    if data['type'] == "sample":
        target = crits_page + "samples/"
    elif data['type'] == "domain":
        target = crits_page + "domains/"
    elif data['type'] == "ip":
        target = crits_page + "ips/"
    elif data['type'] == "raw_data":
        target = crits_page + "raw_data/"
    elif data['type'] == "event":
        target = crits_page + "events/"
    elif data['type'] == "relationship":
        target = crits_page + "relationships/"
    else:
        target = "Type does not exist"

    #Add API Login to end of CRITs Request
    target = target + "?" + crits_login

#    req = urllib2.Request(target)

#    method = "POST"
#    url_data = urllib.urlencode(data)
#    req = urllib2.Request(target, data=url_data)
#    req.get_method = lambda: method

    register_openers()
    method = "POST"
    datagen, headers = multipart_encode(data)
    req = urllib2.Request(target, datagen, headers)
    req.get_method = lambda: method    

    try:
        response = urllib2.urlopen(req)
    except urllib2.URLError, e:
        upload_object_res = "Bad response code %s" % e
        return upload_object_res

    httpResponse = response.getcode()
    if httpResponse == 200:
        json_result = response.read()
        try:
            result = json.loads(json_result)
        except ValueError, e:
            upload_object_res = "JSON load error %s" % e
            return upload_object_res
        size = len(result)
        if size == 0:
            upload_object_res = "Response size 0"
            return upload_object_res

    return result


def build_data(pre_data, last_sample, newname=""):

    # crits_page = "https://192.168.1.131/api/v1/"
    # crits_login = "username=<username>&api_key=<api_key>"

    dt = time.strftime("%Y-%m-%d %H:%M:%S")
    dt = dt+".000"

    data = { "type": pre_data['type'],
             "source": crits_source,
             "ticket": str(last_sample.ticket) }

    #Set parameters unique to each data type
    if pre_data['type'] == "sample":
        data['upload_type'] = "file"
        data['file_format'] = "raw"
        data['filedata'] = open(str(newname))

    if pre_data['type'] == "sample_metadata":
        data['type'] = "sample"
        data['upload_type'] = "metadata"
        data['filename'] = pre_data['val']['filename']
        data['md5'] = pre_data['val']['md5']

    if pre_data['type'] == "domain":
        data['domain'] = pre_data['val']

    if pre_data['type'] == "ip":
        data['ip'] = pre_data['val']
        data['ip_type'] = "Address - ipv4-addr"

    if pre_data['type'] == "ticket":
        data['type'] = "event"
        data['event_type'] = "Incident"
        data['title'] = last_sample.ticket
        data['description'] = "This incident is from ticket # " + last_sample.ticket
        data['date'] = dt

    if pre_data['type'] == "ua": # User-Agents
        data['type'] = "event"
        data['event_type'] = "Indicators - Network Activity"
        data['title'] = pre_data['val']
        if pre_data['ta'] == True:
            data['description'] = "This User-Agent was added from a ThreatAnalyzer callout in ticket # " + last_sample.ticket
        else:
            data['description'] = "This User-Agent was manually added for ticket # " + last_sample.ticket
        data['date'] = dt

    if pre_data['type'] == "command":
        data['type'] = "event"
        data['title'] = pre_data['val']
        data['event_type'] = "Indicators - Network Activity"
        if pre_data['ta'] == True:
            data['description'] = "This URI was added from a ThreatAnalyzer callout in ticket # " + last_sample.ticket
        else:
            data['description'] = "This URI was manually added for ticket # " + last_sample.ticket
        data['date'] = dt

    if pre_data['type'] == "vt":
        data['type'] = "event"
        data['title'] = pre_data['val']
        data['event_type'] = "Malware Samples"
        data['description'] = "This sample was added from VirusTotal results in Ticket # " + last_sample.ticket
        data['date'] = dt

    if pre_data['type'] == "relationship":
        data['left_type'] = pre_data['tlo1type']
        data['left_id'] = pre_data['tlo1id']
        data['right_type'] = pre_data['tlo2type']
        data['right_id'] = pre_data['tlo2id']
        data['rel_type'] = "Related_To"
        data['rel_reason'] = "Related via ticket " + last_sample.ticket

    return data
    

def submit_to_crits(post_data, last_sample, crits_ta, savename=""):

    crits_result = {}
    crits_str_result = ""
    crits_upload_dict = {}
    data = {}
    final_data = {}
    search_res = {}

    for k, v in post_data.iteritems():
        if "chk" in k and v=="on":
            chk_input_key = re.sub("_chk", "", k)
            if post_data[chk_input_key] : #Make sure there's an input that matches with the checkbox

                #Create or clear the dict if it already exists
                data, final_data = clear_upload_dicts(data, final_data)
                search_res.clear()

                if "_domain_" in chk_input_key:
                    data['type'] = "domain"
                elif "_ip_" in chk_input_key:
                    data['type'] = "ip"
                elif "_vt_" in chk_input_key:
                    data['type'] = "vt"
                elif "_command_" in chk_input_key:
                    data['type'] = "command"
                elif "_ua_" in chk_input_key:
                    data['type'] = "ua"
                else:
                    data['type'] = "event"

                data['val'] = post_data[chk_input_key]
                if not data['val']: #If the input is empty, check the next form box
                    continue

                if "ta_" in chk_input_key:
                    data['ta'] = True
                else:
                    data['ta'] = False

                # Search if the object already exists. If  it does, pull in the JSON, otherwise, add it to CRITs

                data['search'] = data['val']

                search_res = search_crits(data)

                # Set types for relationships later on
                if data['type'] == "domain":
                    crits_type = "Domain"
                elif data['type'] == "ip":
                    crits_type = "IP"
                else:
                    crits_type = "Event"


                if search_res['objects']: # If result found

                    if data['type'] not in crits_upload_dict: # If a list for that type does not yet exist, create it
                        crits_upload_dict[data['type']] = []
 
                    crits_upload_dict[data['type']].append({"id": search_res['objects'][0]['_id'],
                                                                     "type": crits_type})


                else: # If no result found in search, add it to CRITs

                    final_data = build_data(data, last_sample)
                    crits_upload_res = upload_object(final_data)
                    crits_str_result += "uploaded " + data['type'] + "\r\n\r\n" + str(crits_upload_res) + "\r\n\r\n*************\r\n\r\n"


                    if data['type'] not in crits_upload_dict:  # If a list of that type does not yet exist, create it
                        crits_upload_dict[data['type']] = []

                    crits_upload_dict[data['type']].append({"id": crits_upload_res['id'],
                                                            "type": crits_type})
                    

    #################################################
    #### Handle Uploading the Ticket as an Event ####
    #################################################

    #Create or clear the dict if it already exists
    data, final_data = clear_upload_dicts(data, final_data)
    search_res.clear()

    data['type'] = "ticket"

    # Search if the ticket already exists. If it does, pull in the JSON, otherwise, add it to CRITs
    data['search'] = last_sample.ticket
    search_res = search_crits(data)

    if search_res['objects']: # If an event with this Ticket # is found to exist, use its existing ID
        crits_upload_dict['ticket'] = [{'id': search_res['objects'][0]['_id'],
                                        'type': 'Event'}]
#        crits_upload_dict['ticket'][0]['id'] = search_res['objects'][0]['_id']
#        crits_upload_dict['ticket'][0]['type'] = 'Event'
    else: # Otherwise, upload it
        final_data = build_data(data, last_sample)
        crits_upload_res = upload_object(final_data)

        crits_upload_dict['ticket'] = [{'id': crits_upload_res['id'],
                                        'type': 'Event'}]
        crits_str_result += "\r\nUploaded Ticket: " + str(crits_upload_dict['ticket'][0]) + "\r\n\r\n***********************\r\n"


    ############################################
    #### Handle uploading the sample itself ####
    ############################################

    #Create or clear the dict if it already exists
    data, final_data = clear_upload_dicts(data, final_data)
    search_res.clear()

    data['type'] = "sample"

    # Search if the sample already exists. If it does, pull the JSON, otherwise, add it to CRITs
    data['search'] = last_sample.md5
    search_res = search_crits(data)

    if search_res['objects']:
        crits_upload_dict['sample'] = [{'id': search_res['objects'][0]['_id'],
                                        'type': 'Sample'}]

    else:
        # Need to handle renaming the sample to remove the .MAL when adding to CRITs
        # Before calling build_data()

        savename = "sanalysis/static/"+savename
        newname = threatanalyzer.remove_mal(savename) # Copy the file without .MAL - Removed later in main method
        
        final_data = build_data(data, last_sample, newname=newname)
        crits_upload_res = upload_object(final_data)

        rem_tmp = threatanalyzer.remove_tmp_file(newname) # Remove the copy of the file that doesn't have .MAL


        crits_upload_dict['sample'] = [{'id': crits_upload_res['id'],
                                        'type': 'Sample'}]

        crits_str_result += "\r\nUploaded Sample: \r\n" + str(crits_upload_dict['sample'][0]) + "\r\n\r\n****************\r\n\r\n"

    ########################################################
    #### Handle uploading metadata of any dropped files ####
    ########################################################
    
    if crits_ta['crits_dropped']:
        crits_upload_dict['sample_metadata'] = []
        for dropped in crits_ta['crits_dropped']:
        
            data, final_data = clear_upload_dicts(data, final_data)
            search_res.clear()
            
            data['type'] = "sample_metadata"
            data['val'] = dropped
            
            data['search'] = dropped['md5']
            search_res = search_crits(data)
            
            if search_res['objects']:
                crits_upload_dict['sample_metadata'].append({'id': search_res['objects'][0]['_id'],
                                                         'type': 'Sample'})
                                                         
            else:
                final_data = build_data(data, last_sample)
                crits_upload_res = upload_object(final_data)
                
                crits_upload_dict['sample_metadata'].append({'id': crits_upload_res['id'],
                                                         'type': 'Sample'})
                crits_str_result += "\r\nUploaded Sample MetaData: \r\n" + str(crits_upload_dict['sample_metadata'][-1]) + "\r\n\r\n****************\r\n\r\n"



    ##################################
    #### Handle all relationships ####
    ##################################

    data, final_data = clear_upload_dicts(data, final_data) #Clear dicts

    crits_str_result += "\r\n\r\n****************crits_upload_dict*****************\r\n\r\n" + str(crits_upload_dict)

    relation_res = relate_objects(crits_upload_dict, last_sample)

    crits_str_result += "\r\n\r\n****************relation_res*****************\r\n\r\n" + str(relation_res)

    return crits_str_result

    

def clear_upload_dicts(data, final_data):
    data.clear()
    final_data.clear()
    return data, final_data

def relate_objects(crits_upload_dict, last_sample):
    relation_res = ""
    data = {}
    debug_str=[]
    for obj_key, obj_val in crits_upload_dict.items(): # Start stepping through each TLO
        data['type'] = "relationship"
        while obj_val:
            v = obj_val[0]
            compare1 = str(v['id']) # Set comparison string to avoid relating a TLO to itself

            data['tlo1type'] = v['type']
            data['tlo1id'] = v['id']
            
            for obj_key2, obj_val2 in crits_upload_dict.items(): # Step through each TLO                
                for v2 in obj_val2: # Step through each instance of the current TLO
                
                    compare2 = str(v2['id']) # Set second comparison string
                    
                    if compare1 != compare2: # Make sure we're not relating a TLO to itself
                        data['tlo2type'] = v2['type']
                        data['tlo2id'] = v2['id']
                        
                        if v2['id']:
                    
                            final_data = build_data(data, last_sample)
                            relation_res += "\r\n\r\n" + "Relating " + data['tlo1type'] +" "+ data['tlo1id']
                            relation_res += " to " + data['tlo2type'] +" "+ data['tlo2id'] + "\r\n"
                            relation_res += str(upload_object(final_data))
                            
                            
            
            obj_val.remove(v)

    #errordebug
    return relation_res
                        


def search_crits(data):

    if data['type'] == "sample" or data['type'] == "sample_metadata":
        target = crits_page + "samples/?c-md5=" + data['search']
    if data['type'] == "domain":
        target = crits_page + "domains/?c-domain=" + data['search']
    if data['type'] == "ip":
        target = crits_page + "ips/?c-ip=" + data['search']
    if data['type'] == "event" or data['type'] == "ua" or data['type'] == "vt" or data['type'] == "command" or data['type'] == "ticket":
        search = urllib.quote_plus(data['search'])
        target = crits_page + "events/?c-title=" + search

    target = target + "&" + crits_login

    req = urllib2.Request(target)

    try:
        response = urllib2.urlopen(req)
    except urllib2.URLError, e:
        search_res = "Bad response code %s" % e
        return search_res

    httpResponse = response.getcode()
    if httpResponse == 200:
        json_result = response.read()
        try:
            result = json.loads(json_result)
        except ValueError, e:
            search_res = "JSON load error %s" % e
            return search_res
        size = len(result)
        if size == 0:
            search_res = "Response size is 0"
            return search_res

    return result

