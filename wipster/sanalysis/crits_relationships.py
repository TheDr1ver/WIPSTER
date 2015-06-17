import requests, json, collections, re
import pprint

pp = pprint.PrettyPrinter(indent=4)


#Make the API call to get the json response from the CRITs server
def call_api(crits_rel_trace_vars):
    if not crits_rel_trace_vars['cid']:
        if crits_rel_trace_vars['type'] == 'Sample':
            url = crits_rel_trace_vars['crits_page']+'samples/?c-md5='+crits_rel_trace_vars['md5']
    else:
        if crits_rel_trace_vars['type'] == 'Sample':
            url = crits_rel_trace_vars['crits_page']+'samples/'+crits_rel_trace_vars['cid']
        elif crits_rel_trace_vars['type'] == "Event":
            url = crits_rel_trace_vars['crits_page']+'events/'+crits_rel_trace_vars['cid']
        elif crits_rel_trace_vars['type'] == "Domain":
            url = crits_rel_trace_vars['crits_page']+'domains/'+crits_rel_trace_vars['cid']
        elif crits_rel_trace_vars['type'] == "IP":
            url = crits_rel_trace_vars['crits_page']+'ips/'+crits_rel_trace_vars['cid']
            
        #Took out campaigns b/c they were related to a LOT, and caused a lot of noise
        #Maybe later I could add somewhere to pull in related campaigns and just de-dup
        #and display them at the top of the results (i.e. Possible related tickets/Possible related campaigns)
        
        #elif crits_rel_trace_vars['type'] == "Campaign":
        #    url = crits_rel_trace_vars['crits_page']+'campaigns/'+crits_rel_trace_vars['cid']
        
        else:
            return None
    params = {
                'api_key': crits_rel_trace_vars['api_key'],
                'username': crits_rel_trace_vars['username'],
                'only': 'relationships,tickets',
             }
             
    r = requests.get(url, params=params, verify=False)
    return json.loads(r.text)


#Walk through the dictionary until you get to an "end" TLO. As long as the while loop hasn't
#run out from get_relationships2, it will make API calls to get the data for each related TLO
#of the TLO it is currently looking at
def dict_walk(tlo_key, tlo_dict, crits_rel_trace_vars):

    if isinstance(tlo_dict, dict):
                
        #First walking of 'master' runs through checking if each attribute is a dictionary
        #It gets to the 'rels' dictionary, realizes it's a dictionary, and starts the recursive loop
        #Now we're looking at the 'rels' dictionary, which contains multiple dictionaries
        #The first one we hit is the 'rels' '1' dictionary
        #We see 'rels' '1' is a dictionary, so we start walking through that
        #Now all we have is '_id', 'rels', and 'type'. 'rels' is a dictionary, BUT it doesn't have any data
        #So now it stops walking, and starts moving on to 'if tlo_key in tlo_dict'
        #So, if 'rels' is in master (which it is), we start...
        #...Iterating through the the items in master['rels']
        #Now we're looking at master['rels']['1']
        #If '1' is not in master['rels'] (which it is... and always will be... This section can probably go)
        #Then make 1 a dictionary, then add to it a property of 'rels' as an empty dict
        #Set the next API call to look up whatever the current '_id' field is set to & make the call
        #After it makes the call, get all the relationships and walk through them one at a time
        #Set the first group of rels as 1c1, etc...
        #Once these are populated, we'll be looping through the second time.
        
        #This time we're re-feeding our existing dict (starting with master)
        #Is master['rels'] a dict? Yes, loop through it.
        #Is master['rels']['1'] a dict? Yes, loop through it.
        #Is master['rels']['1']['rels'] a dict? Yes, loop through it.
        #Is master['rels']['1']['rels']['1c1'] a dict? Yes, loop through it.
        #No more dictionaries in '1c1', so move on to next instruction
        
        #Going through master['rels']['1']['rels']
        #### The problem was that the last walk was overwriting rel['rels'][counter_str] with a blank dict 
        #### because it wasn't checking if it existed first
        
        for k, v in tlo_dict.items():
            dict_walk(tlo_key, v, crits_rel_trace_vars)
        
        if tlo_key in tlo_dict:
            for idx,rel in tlo_dict[tlo_key].items():
                idx = str(idx)
                if idx not in tlo_dict[tlo_key]:    #This If is probably not necessary, b/c idx should always be in tlo_dict[tlo_key]
                    tlo_dict[tlo_key][idx] = {}
                    tlo_dict[tlo_key][idx]['rels']={}
                crits_rel_trace_vars['cid'] = rel['_id']
                crits_rel_trace_vars['type'] = rel['type']
                
                #Make the API call for the _id being looped through
                response = call_api(crits_rel_trace_vars)
                if response:    #if it doesn't match one of the TLO types we're checking for, don't do anything else
                    tlo_dict[tlo_key][idx]['tickets'] = {}
                                
                    if 'relationships' in response:
                        counter = 1
                        for relationship in response['relationships']:
                            #TODO: Add ignore list (ex. if not in crits_rel_ignore: -- such as Mozilla/4.0*)
                            counter_str = "c"+str(counter)  #Build the sub-counter index
                            counter_str = idx+counter_str
                            rel_id = relationship['value']
                            rel_type = relationship['type']
                            rel_dict = {'_id': rel_id,
                                        'type': rel_type,
                                        'rels' : {}}
                                        
                            #This is important - make sure the sub-counter index doesn't already exist
                            #If it does, and this check wasn't made, it would overwrite everything with a blank 'rels' dict
                            if counter_str not in rel['rels']:  
                                rel['rels'][counter_str]=rel_dict
                            counter += 1
                        
                    if 'tickets' in response:
                        counter = 1
                        for ticket in response['tickets']:
                            if ticket['ticket_number'] not in tlo_dict[tlo_key][idx]['tickets'].values():
                                counter_str = "t"+str(counter)
                                counter_str = idx+counter_str
                                tlo_dict[tlo_key][idx]['tickets'][counter_str]=ticket['ticket_number']
                                counter+=1
                    
                    tlo_dict[tlo_key][idx]['name'] = get_tlo_name(response)

            #print json.dumps(tlo_dict)

                            
    elif isinstance(tlo_dict, (list, tuple)):
        for v in tlo_dict:
            dict_walk(tlo_key, v, crits_rel_trace_vars)
            
    return tlo_dict

#Get the relationships for the sample currently being inspected in WIPSTER,
#...and the relationships of that... And the relationships of that...
#...until the 'depth' setting has been reached.    
def get_relationships2(crits_rel_trace_vars):

    request = {'master': {}}
    ticket = {}

    
    while_depth = len(crits_rel_trace_vars['depth'])
    
    while while_depth:
    
        if not request['master']: #If this is the first (original Sample) TLO
            #Get the API request for the sample at hand
            response = call_api(crits_rel_trace_vars)
            response = response['objects'][0]
            if response:
                request['master']['_id'] = response['_id']
                crits_rel_trace_vars['cid'] = response['_id']
                request['master']['md5'] = response['md5']
                request['master']['name'] = get_tlo_name(response)
                crits_rel_trace_vars['type'] = 'Sample'
                request['master']['rels'] = {}
                request['master']['tickets'] = {}
                if 'tickets' in response:
                    counter = 1
                    for ticket in response['tickets']:
                        counter_str = str(counter)
                        if ticket not in request['master']['tickets'].values():
                            request['master']['tickets'][counter_str]=ticket
                            counter +=1
                if 'relationships' in response:
                    counter = 1
                    for relationship in response['relationships']:
                        #TODO: Add ignore list (ex. if not in crits_rel_ignore: -- such as Mozilla/4.0*)
                        counter_str = str(counter)
                        rel_id = relationship['value']
                        rel_type = relationship['type']
                        rel_dict = {'_id': rel_id,
                                    'type': rel_type,
                                    'rels': {}}
                        request['master']['rels'][counter_str]=rel_dict
                        counter+=1

            else:
                request['error'] = "No response given from CRITs"
                print "ERROR - No response given from CRITs"
                
        #If this is not the first (original Sample) TLO, walk the dict, searching for multi-tiered relationships until
        #the While loop ends (whatever the depth is set to)
        else:   
            dict_walk('rels', request, crits_rel_trace_vars)
        
        while_depth-=1

    #print "Final result for post-processing:"    
    #print json.dumps(request)
    
    #post_call_dict_walk('rels', request)
    
    #print list(keypaths(request))
    
    #Build the reverse dictionary for looking up potentially related ticket numbers, and how they were found via the main dict
    reverse_dict = {}
    for keypath, value in keypaths(request):
        if 'tickets' in keypath and repInt(value):    #only add to the reverse dictionary for lookup if it's a ticket number
            reverse_dict.setdefault(value, []).append(keypath)
        
    return request, reverse_dict

#Helper-function - check if a string can be converted to an integer
#This keeps us from adding stuff like analyst, timestamp, domain, etc
#to the reverse-lookup dict
def repInt(s):
    try:
        int(s)
        return True
    except ValueError:
        return False

#Reverse-trace the dictionary for each ticket found that's not currently related to the
#main sample being observed in WIPSTER        
def trace_rels(reverse_dict, tlo_dict, ticket):   
    
    ticket_trace = reverse_dict[ticket]
    trace_result_list = []
    #print "Tracing these tickets: " 
    #for x in ticket_trace:
    #    print x
    for i in ticket_trace:
        trace_result=""
        #print "following this trace: "+str(i)
        #print "starting wth this key: "+i[0]
        trace_add = trace_dict_walk(i[0], tlo_dict, i, trace_result)
        if trace_add not in trace_result_list:  #Dedup
            trace_result_list.append(trace_add)
    
    return trace_result_list

#Reverse-trace the dictionary to build the final string for each individual link/trace
#(ex. bad.exe [cid] -> badguy.com [cid] -> Ticket: 12345
def trace_dict_walk(tlo_key, tlo_dict, ticket_trace, trace_result):
    
    if isinstance (tlo_dict, dict):
        #print "tlo_dict is a dict: "+str(tlo_dict)
        #print "Looking for this key in it: "+str(tlo_key)
        #print "ticket_trace is currently: "+str(ticket_trace)
        if tlo_key in tlo_dict:
            if 'name' in tlo_dict[tlo_key]:
                trace_result += tlo_dict[tlo_key]['name']+" ["
            if '_id' in tlo_dict[tlo_key]:
                trace_result += tlo_dict[tlo_key]['_id']+"] -> "
        
        #tlo_key = ticket_trace[1]
        if len(ticket_trace)>1:
            trace_result = trace_dict_walk(ticket_trace[1], tlo_dict[tlo_key], ticket_trace[1:], trace_result)
        else:
            trace_result +="Ticket: "+tlo_dict[tlo_key]
    
    return trace_result
            
#Make the reverse-lookup dictionary used in get_relationships2
def keypaths(nested):
    for key, value in nested.iteritems():
        if isinstance(value, collections.Mapping):
            for subkey, subvalue in keypaths(value):
                yield [key] + subkey, subvalue
        else:
            yield [key], value

            
#Depending on what type of TLO it is, return the 'name' of the TLO
def get_tlo_name(tlo):
    if 'filename' in tlo:
        last_tier = tlo['filename']
    elif 'domain' in tlo:
        last_tier = tlo['domain']
    elif 'ip' in tlo:
        last_tier = tlo['ip']
    elif 'title' in tlo:
        last_tier = tlo['title']
    else:
        last_tier = None
        
    return last_tier


#Trace Relationships to Possible Related Tickets
def trace_crits_relationships(crits_rel_trace_vars):

    possible_tickets = {}
    relationship_trace = ""

    #Get the full dictionary of all related TLO's and their related TLO's etc... until the depth is reached
    #Also get a reverse-lookup for that dictionary
    tlo_dict, reverse_dict = get_relationships2(crits_rel_trace_vars)

    #Check to see if any tickets are found in the full relationship dictionary that are not directly related to the sample in question
    #i.e. if bad.exe is related to badguy.com, and badguy.com is related to ticket 555, but bad.exe is NOT related to ticket 555 directly,
    #then we want to know about it.
    for k, v in reverse_dict.items():
        if k not in crits_rel_trace_vars['db_tickets']: 
            ticket_trace = trace_rels(reverse_dict, tlo_dict, k)
            possible_tickets[k]=ticket_trace    #Set the key of the ticket number to possible_tickets dict, 
                                                #with value as a list of each trace found for that ticket
            
            #Sort possible_tickets[k] (ex. possible_tickets['12345']) so that the least amount of jumps to a potentially related ticket
            #comes first. (i.e. bad.exe -> badguy.com -> ticket 555 (2 jumps) is a higher priority than bad.exe -> Mozilla/4.0 -> badguy.com -> 555 (3 jumps))
            
            possible_tickets[k] = sorted(possible_tickets[k], key=lambda kvt: kvt.count(" -> "))


    #Sort possibly related tickets in order from highest number of relations to lower number of relations
    #(i.e. if our sample has 10 ways of connecting it to ticket 555, that's potentially more interesting than 
    # if it has only one way of connecting to ticket 777)
    
    possible_tickets = sorted(possible_tickets.iteritems(), key=lambda kvt: len(kvt[1]), reverse=True)

    relationship_trace+="Current Tickets related to this Sample in WIPSTER: "
    for i in crits_rel_trace_vars['db_tickets']:
        relationship_trace+=str(i)+" "
    relationship_trace+="\r\n\r\n"
    
    for k, v in possible_tickets:
        relationship_trace+="Possible related ticket found: "+k+"\r\n"
        for i in v:
            #### TODO: Regex for cid's and add HTML to link to CRITs page
            #### Link will be for https://<your CRITs IP>/search/?q=<cid of the TLO>&search_type=global
            i = re.sub(r'(\[)([a-z0-9]{24})(\] ->)', 
                        '\\1<a href="%s/search/?q=\\2&search_type=global" target="_blank">\\2</a>\\3' % crits_rel_trace_vars['crits_base'], 
                        i)
            relationship_trace += i+"\r\n"
        relationship_trace += "\r\n"
        
    return relationship_trace

   
   
#Debug Testing   
#relationship_trace = trace_crits_relationships(crits_rel_trace_vars)
#print relationship_trace