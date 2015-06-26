import urllib2, re, cgi, jsbeautifier
from re import findall



def get_html(uri, html, js):
    #Get initial HTML
    #Make sure it's valid HTML with a 200 response
    #New function for finding redirects/iframes
    #Get HTML of redirects/iframes
    #Return big block of HTML, separated by a delimiter "<!--WIPSTER SPLIT HTML-->"
    print "\r\n****URI: "+uri+"****\r\n"
    req = urllib2.Request(uri)
    try:
        response = urllib2.urlopen(req)
        this_check = response.read()
        this_check = this_check.decode()
        html += "\r\n\r\n <!--WIPSTER SPLIT HTML ["+uri+"]--> \r\n\r\n"
        #html += cgi.escape(this_check, quote=True) #Put this back in when testing is over to sanitize DB content
        html += this_check
        
        '''
        #Come back to this later...
        print "Response Code: "+str(response.code)
        if response.code in (300, 301, 302, 303, 304, 307):
            print str(response.code)+" redirect"
        '''
        
        success = True
        
        print "\r\n***HTML***\r\n"
        print this_check
        
    except URLError as e:
        html += "ERROR: "+e.reason
        success = False
    #breakdebug
    
    if success: #if the page is successfully grabbed...
        redirects = []
        #...scrape it for iframes and redirects...
        redirects.extend(scrape(this_check, "iframe"))
        redirects.extend(scrape(this_check, "redir"))
        for redir in redirects:
            html, js = get_html(redir, html, js)    # ...and follow them too.
            
        #Then scrape for javascripts
        javascripts = scrape(this_check, "js")  #Get on-page Javascripts
        for script in javascripts:
            js+="//WIPSTER SPLIT JAVASCRIPT ["+uri+"] \r\n\r\n"
            
            #jsbeautifier
            script = jsbeautifier.beautify(script)
            #js-didier
            #jsDetox?
            
            js+=script
            
        js_embed = scrape(this_check, "js_embed")   #Get externally-hosted JavaScripts
        for script in js_embed:
            js+="//WIPSTER SPLIT JAVASCRIPT ["+script+"] \r\n\r\n"
            jsreq = urllib2.Request(script)
            try:
                jsresponse = urllib2.urlopen(jsreq)
                js_check = response.read()
                js_check = js_check.decode()
                #html += "\r\n\r\n <!--WIPSTER SPLIT HTML ["+uri+"]--> \r\n\r\n"
                js += js_check                
                success = True
                                
            except URLError as e:
                js += "ERROR: "+e.reason
                success = False
            
            
        
    
    #If it's not an error, and a valid 200 response,
        #Check the response HTML for redirects/iframes
            #For each redirect/iframe, call this subroutine again w/ the individual URL
        #Checking for JavaScript will be done after the full HTML block is returned, and will be called from views.py
    
    #Add the result to the master HTML list w/ "<!--WIPSTER SPLIT HTML-->" as a breaker between each response
    
    return html, js
    
def scrape(this_check, type):
    #Go line-by-line to check for redirects/iframes
    #If they're found, add to a list to be checked
    #Return the list
    results = []
    if type == "iframe":
        results = findall("<iframe.*src=\"(.*)\"", this_check)
    elif type == "redir":
        results = findall("http-equiv=\"refresh\".*content=\".*url=(.*)\"", this_check)
        results.extend(findall("window\.location\.href=\"(.*)\"", this_check))
    elif type == "js":
        #print "\r\nScraping for javascript in the following HTML:\r\n"
        #print this_check
        pattern = re.compile("(<script.*</script>)", re.DOTALL)
        results = findall(pattern, this_check)
    elif type == "js_embed":
        results = findall("<script.*src=\"(.*)\"", this_check)
        
    print "\r\n\t***Scrape Results for "+type+" ***\r\n"
    print "\t"+str(results)
    return results
        
        
        
        
        
        
        
    
uri = "http://192.168.1.116/scrape"
html = ""
js = ""
html, js = get_html(uri, html, js)
print "\r\n***FINAL, FULL HTML***\r\n"
print str(html)
print "\r\n***FINAL JS RESULTS***\r\n"
print js