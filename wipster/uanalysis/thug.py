import os, subprocess, hashlib, sys, pprint
import os.path
from re import findall
#from uanalysis.settings import *


pp = pprint.PrettyPrinter(indent=4)

def get_thug(uri, ua):
    #These will be removed and pulled in from 'settings' file later
    thug_loc="/opt/remnux-thug/src/thug.py"
    es_loc = "/opt/remnux-didier/extractscripts.py"

    results = {}
    md5 = hashlib.md5(uri).hexdigest()
    
    #cmd = ["python", thug_loc, uri]
    #cmd = [thug_loc+" "+uri+" && pwd"]
    #cmd = ["echo blah"]
    cmd = [thug_loc+" -F -n ./static/urls/"+md5+" "+uri]
    run = subprocess.Popen(cmd,
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE,
                           stderr=subprocess.PIPE,
                           shell=True)
    
    #print str(run.communicate())
    #thug_res = run.communicate()
    #breakdebug
    
    thug_res = run.communicate()[1]
    results['thug_res']=thug_res
    
    #Get filenames and their corresponding directories
    thug_files = findall("\(Content-type: (.*), MD5: (.*)\)", thug_res)
    
    #Get URI's that match the MD5's
    md5_url_pre = findall("URL: (.*)\s{1}\(Content-type:.*MD5: (.*)\)", thug_res)
    md5_url_pair = []
    for i in md5_url_pre:
        if i not in md5_url_pair:   #Deduplicate
            md5_url_pair.append(i)
    #print str(thug_files)
    
    file_list=[]
    for file in thug_files:
        subdir = file[0]
        filename = file[1]
        combined = str(subdir)+"/"+str(filename) #Get the path of the file created by Thug
        if combined not in file_list:   #Deduplicate
            file_list.append(combined)
    #print "File_List"
    #print str(file_list)
    
    results['html']=""
    for file in file_list: #For each file created by Thug... 
        
        #...get the HTML from it...
        results = get_html(file, md5, md5_url_pair, results)
        
        #...get any downloaded files from it...
        
        #...run didier's extractscripts.py against it
        run_extractscripts(file, md5, es_loc)
        # Now all the extracted scripts are in /wipster/uanalysis/static/urls/<md5>/
        
    #Run js-didier against all scripts extracted
    base_dir = "./static/urls/"+md5+"/"
#   file_list = []
    results['js_didier']=""
    results['js'] = ""
    
    #Process JavaScript
    results = get_js(base_dir, results)
    

                    
            
    
    return results

def get_html(file, md5, md5_url_pair, results):
    if "text/html" in file:
        full_dir = "./static/urls/"+md5+"/"+file
        f = open(full_dir)
        data = f.read()
        file_md5 = hashlib.md5(data).hexdigest()
        
        for pair in md5_url_pair:
            #print "Checking for MD5 "+md5+" in pair "+str(pair)
            #print pair
            if file_md5 in pair:    #If the md5 of the file we're looking at matches up with one in the pair set...
                #print file_md5+" is in "+str(pair)
                
                #...Then we know the full URI of that was requested, and can make note of it
                results['html']+="\r\n<!-- WIPSTER HTML FOR "+pair[0]+" -->\r\n"
                results['html']+=data   #This should be HTML-escaped later
                
    return results
    
def run_extractscripts(file, md5, es_loc):
    #chg_dir = "./static/urls/"+md5+"/"+file[:-32]
    chg_dir = "./static/urls/"+md5+"/"+file+"_files"
    #Make a new directory, move the existing file to that directory, then cd to new directory, run extractscripts, and bounce back to original dir
    cmd = ["mkdir "+chg_dir+" && mv "+chg_dir[:-6]+" "+chg_dir+" && cd "+chg_dir+" && "+es_loc+" "+file[-32:]+" && cd -"]
    print str(cmd)
    run = subprocess.Popen(cmd,
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE,
                           stderr=subprocess.PIPE,
                           shell=True)
    #print "ExtractScripts Results:"
    #print str(run.communicate())
    extracted = run.communicate()

def get_js(base_dir, results):
    for dirpath, dirnames, filenames in os.walk(base_dir):
        #Look for files starting with 'script' in subdirectories
        for filename in [f for f in filenames if f.startswith("script")]:
            #If script file found, read it and add it to results['js']
            full_name = os.path.join(dirpath, filename)
            fjs = open(full_name)
            js_content = fjs.read()
            results['js']+="\r\n<!-- WIPSTER SPLIT "+filename+" -->\r\n"
            results['js']+=js_content
            #Then run it against js-didier
            cmd = ["cd "+dirpath+" && js-didier "+filename+" && cd -"]
            #print "running cmd: "+str(cmd)
            run = subprocess.Popen(cmd,
                                   stdout=subprocess.PIPE,
                                   stdin=subprocess.PIPE,
                                   stderr=subprocess.PIPE,
                                   shell=True)
            #print(str(run.communicate()))
            run.communicate()
            js_files = []
            
            for dirpath2, dirnames2, filenames2 in os.walk(base_dir):
                #Look for files ending in .log
                for filename2 in [f2 for f2 in filenames2 if f2.endswith(".log")]:
                    #If js-didier results found, read it and add it to results['js-didier']
                    full_name2 = os.path.join(dirpath2, filename2)
                    fjs2 = open(full_name2)
                    didier_content = fjs2.read()
                    results['js_didier']+="\r\n<!-- WIPSTER SPLIT "+filename+" -> "+filename2+" -->\r\n"
                    results['js_didier']+=didier_content
                    
    return results
    
#uri = "http://192.168.1.116/scrape/"
uri = "http://jonkjarangmailcom.altervista.org/Docu16527665041437737688738377282220515/indkex3.html"

ua = "winxpie60"
results = get_thug(uri, ua)

#for x, y in results.iteritems():
#    print x, y
pp.pprint(results['js_didier'])
pp.pprint(results['js'])