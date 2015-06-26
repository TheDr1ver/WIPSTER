from uanalysis.settings import *
from django.core.validators import URLValidator
from django.core.exceptions import ValidationError
from .models import URL
import urllib2, hashlib, subprocess, sys, os, cgi, re
import os.path
from re import findall

def get_thug(uri, ua):
    #These will be removed and pulled in from 'settings' file later
    thug_loc="/opt/remnux-thug/src/thug.py"
    es_loc = "/opt/remnux-didier/extractscripts.py"

    results = {'thug_res': '',
               'html': '',
               'js_didier': '',
               'js': ''}
    #results['html']=""
    #results['js_didier']=""
    #results['js'] = ""
    md5 = hashlib.md5(uri).hexdigest()
    
    #cmd = ["python", thug_loc, uri]
    #cmd = [thug_loc+" "+uri+" && pwd"]
    #cmd = ["echo blah"] 
    
    #Check if URI is a valid URL
    #If not, return 'thug_res' as "Invalid URL - Thug did not run"
    validate = URLValidator()
    if uri.startswith('http'):
        uri_test = uri
    else:
        uri_test = "http://"+uri
        
    try:
        validate(uri_test)
    except ValidationError, e:
        results['thug_res']="Invalid URI. Thug was not run."
        return results

    #Match UserAgent to list
    ua_list = {'Internet Explorer 6.0 (Windows XP)': 'winxpie60',
               'Internet Explorer 6.1 (Windows XP)':'winxpie61',
               'Internet Explorer 7.0 (Windows XP)':'winxpie70',
               'Internet Explorer 8.0 (Windows XP)':'winxpie80',
               'Chrome 20.0.1132.47 (Windows XP)':'winxpchrome20',
               'Firefox 12.0 (Windows XP)':'winxpfirefox12',
               'Safari 5.1.7 (Windows XP)':'winxpsafari5',
               'Internet Explorer 6.0 (Windows 2000)':'win2kie60',
               'Internet Explorer 8.0 (Windows 2000)':'win2kie80',
               'Internet Explorer 8.0 (Windows 7)':'win7ie80',
               'Internet Explorer 9.0 (Windows 7)':'win7ie90',
               'Chrome 20.0.1132.47 (Windows 7)':'win7chrome20',
               'Chrome 40.0.2214.91 (Windows 7)':'win7chrome40',
               'Firefox 3.6.13 (Windows 7)':'win7firefox3',
               'Safari 5.1.7 (Windows 7)':'win7safari5',
               'Safari 5.1.1 (MacOS X 10.7.2)':'osx10safari5',
               'Chrome 19.0.1084.54 (MacOS X 10.7.4)':'osx10chrome19',
               'Chrome 26.0.1410.19 (Linux)':'linuxchrome26',
               'Chrome 30.0.1599.15 (Linux)':'linuxchrome30',
               'Firefox 19.0 (Linux)':'linuxfirefox19',
               'Chrome 18.0.1025.166 (Samsung Galaxy S II, Android 4.0.3)':'galaxy2chrome18',
               'Chrome 25.0.1364.123 (Samsung Galaxy S II, Android 4.0.3)':'galaxy2chrome25',
               'Chrome 29.0.1547.59 (Samsung Galaxy S II, Android 4.1.2)':'galaxy2chrome29',
               'Chrome 18.0.1025.133 (Google Nexus, Android 4.0.4)':'nexuschrome18',
               'Safari 7.0 (iPad, iOS 7.0.4)':'ipadsafari7',
               'Safari 8.0 (iPad, iOS 8.0.2)':'ipadsafari8',
               'Chrome 33.0.1750.21 (iPad, iOS 7.1)':'ipadchrome33',
               'Chrome 35.0.1916.41 (iPad, iOS 7.1.1)':'ipadchrome35',
               'Chrome 37.0.2062.52 (iPad, iOS 7.1.2)':'ipadchrome37',
               'Chrome 38.0.2125.59 (iPad, iOS 8.0.2)':'ipadchrome38',
               'Chrome 39.0.2171.45 (iPad, iOS 8.1.1)':'ipadchrome39'}
               
    if ua in ua_list:
        ua = ua_list[ua]
    else:
        ua = 'winxpie60'

        
    #Run thug with useragent selected from form, max setTimeout/setInterval delay to 5 seconds, overall timeout in 5 minutes
    #Write logs to a file in ./uanalysis/static/urls/<md5>/
    cmd = [thug_loc+" -u "+ua+" -w 5000 -T 300 -F -n ./uanalysis/static/urls/"+md5+" \""+uri+"\""]
    run = subprocess.Popen(cmd,
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE,
                           stderr=subprocess.PIPE,
                           shell=True)
    
    #print str(run.communicate())
    #thug_res = run.communicate()
    #breakdebug
    
    thug_res = cgi.escape(run.communicate()[1])
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
    
    
    for file in file_list: #For each file created by Thug... 
        
        #...get the HTML from it...
        results = get_html(file, md5, md5_url_pair, results)
        
        #...get any downloaded files from it...
        
        #...run didier's extractscripts.py against it
        run_extractscripts(file, md5, es_loc)
        # Now all the extracted scripts are in /wipster/uanalysis/static/urls/<md5>_files/
        
    base_dir = "./uanalysis/static/urls/"+md5+"/"
#   file_list = []
    
    
    #Process JavaScript
    results = get_js(base_dir, results)
    

                    
            
    
    return results
    
def get_html(file, md5, md5_url_pair, results):
    if "text/html" in file:
        full_dir = "./uanalysis/static/urls/"+md5+"/"+file
        f = open(full_dir)
        data = f.read()
        file_md5 = hashlib.md5(data).hexdigest()
        
        for pair in md5_url_pair:
            #print "Checking for MD5 "+md5+" in pair "+str(pair)
            #print pair
            if file_md5 in pair:    #If the md5 of the file we're looking at matches up with one in the pair set...
                #print file_md5+" is in "+str(pair)
                
                #...Then we know the full URI of that was requested, and can make note of it
                #results['html']+="\r\n<!-- WIPSTER HTML FOR "+pair[0]+" -->\r\n"
                #Obfuscate pair[0] so the highlighting regex doesn't affect it
                #results['html']+="<h3>"+pair[0].encode('utf-16')+"</h3><pre>"
                h3url = pair[0].split('.')
                h3url = ".<!---->".join(h3url)
                h3url = h3url.replace('http', 'ht<!---->tp')
                results['html']+="<h3>"+h3url+"</h3><pre>"
                results['html']+=cgi.escape(data)   #This should be HTML-escaped later
                results['html']+="</pre>"
                
    return results
    
def run_extractscripts(file, md5, es_loc):
    if "text/html" in file:
        #chg_dir = "./uanalysis/static/urls/"+md5+"/"+file[:-32]
        chg_dir = "./uanalysis/static/urls/"+md5+"/"+file+"_files"
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
            #results['js']+="\r\n<!-- WIPSTER SPLIT "+filename+" -->\r\n"
            if js_content.strip() and js_content!="//": #Check to make sure the file is not empty
                results['js']+="<h3>Raw JS From "+filename+"</h3>\r\n<pre>\r\n"
                results['js']+=cgi.escape(js_content)
                results['js']+="</pre>"
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
                    #Decode UTF-16 files created by js-didier (and hope this shit doesn't blow up in my face later)
                    try:
                        didier_content = didier_content.decode('utf-16')
                    except UnicodeError:
                        didier_content = didier_content
                    #results['js_didier']+="\r\n<!-- WIPSTER SPLIT "+filename+" -> "+filename2+" -->\r\n"
                    results['js_didier']+="<h3>"+filename+" Deobfuscated -> "+filename2+"</h3>\r\n<pre>\r\n"
                    results['js_didier']+=cgi.escape(didier_content)
                    results['js_didier']+="</pre>"
                    
    return results
    
    
    
    
def get_formatting(data, type):

    if type=="thug":
        data = re.sub(r"(.*Classifier\]\s.*)", "<span class='orange'>\\1</span>", data)
        
    elif type=="url":
        #Highlight URLs red
        #RegEx pulled from https://gist.github.com/gruber/8891611
        #Python pulled from https://gist.github.com/uogbuji/705383
        GRUBER_URLINTEXT_PAT = re.compile(ur'(?miu)\b((?<!<h3>)(?:https?://|www\d{0,3}[.]|[a-z0-9.\-;&\?=]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{}:\'\".,<>\xab\xbb\u201c\u201d\u2018\u2019]))')
        found_urls = GRUBER_URLINTEXT_PAT.findall(data)
        urls = []
        for f_u in found_urls:
            if f_u[0] not in urls:
                urls.append(f_u[0])
            #urls.append(f_u[0])
        #data = str(data)
        
        for u in urls:
            if u in data:
                #u = str(u)
                #Find all the instances of this URI in the data set
                #If they don't start with </h3>, re.sub it for the red span
                data = data.replace(u, "<span class='red'>%s</span>" % u)
        #breakdebug

    elif type=="form_post":
        data = re.sub(r"(&lt;form.*method=\'?\"?post.*&gt;)", "<span class='blue'>\\1</span>", data, flags=re.I)

    return data
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
'''    
def get_html(uri):
    #Get initial HTML
    #Make sure it's valid HTML with a 200 response
    #New function for finding redirects/iframes
    #Get HTML of redirects/iframes
    #Return big block of HTML, separated by a delimiter "<!--WIPSTER SPLIT HTML-->"
    
    req = urllib2.Request(uri)
    try:
        response = urllib2.urlopen(req)
        html = response.read()
    except URLError as e:
        html = e.reason
    #breakdebug
    
    #If it's not an error, and a valid 200 response,
        #Check the response HTML for redirects/iframes
            #For each redirect/iframe, call this subroutine again w/ the individual URL
        #Checking for JavaScript will be done after the full HTML block is returned, and will be called from views.py
    
    #Add the result to the master HTML list w/ "<!--WIPSTER SPLIT HTML-->" as a breaker between each response
    
    return html
    
def get_js(html):
    a=1
    #Look for any blocks of <script></script> in the big HTML block, and
    #run it through js-didier, beautifier, and similar tools
    #Output gets displayed in one big block of clean js 
    #"<!--WIPSTER SPLIT JS-->" for each new script
    #"<!--WIPSTER SPLIT EVAL-->" for each new eval of a single script
    return a
    
def get_vt(uri):
    a=1
    #Runs if vt is set to check URL's
    #Outputs results in text format to feed directly back into the db
    return a
'''