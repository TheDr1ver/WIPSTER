from .models import URL
import re

# Process Search Functions
def process_search(search_term):
    #search_res = URL.objects.filter().order_by('-id')[0]
    #sample = URL.objects.filter(created__lte=timezone.now()).order_by('created')[:25]

    search_split = search_term.split("::")
    
    if len(search_split)>1:
        #search_split[0] = modifier, sha1, filename, etc
        #search_split[1] = actual search
        mod = search_split[0]
        search_term = search_split[1]
        if mod == "md5":
            search_res = URL.objects.filter(md5__iexact=search_term).order_by('created').distinct()
            search_type = "md5"
        if mod == "html":
            search_res = URL.objects.filter(html__iexact=search_term).order_by('created').distinct()
            search_type = "html"
        if mod == "thug":
            search_res = URL.objects.filter(thug__icontains=search_term).order_by('created').distinct()
            search_type = "thug"
#        if mod == "date":
#            search_res = URL.objects.filter(date=search_term).order_by('created').distinct()
#            search_type = "date"
        if mod == "ticket":
            search_res = URL.objects.filter(ticket__iexact=search_term).order_by('created').distinct()
            search_type = "ticket"
        if mod == "vt":
            search_res = URL.objects.filter(vt__icontains=search_term).order_by('created').distinct()
            search_type = "vt"
        if mod == "js":
            search_res = URL.objects.filter(js__icontains=search_term).order_by('created').distinct()
            search_type = "js"
        if mod == "did":
            search_res = URL.objects.filter(js_didier__icontains=search_term).order_by('created').distinct()
            search_type = "js_didier"
        '''
        if mod == "balbuz":
            search_res = URL.objects.filter(balbuzard__icontains=search_term).order_by('created').distinct()
            search_type = "balbuzard"
        if mod == "olemeta":
            search_res = URL.objects.filter(olemeta__icontains=search_term).order_by('created').distinct()
            search_type="olemeta"
        if mod == "vba":
            search_res = URL.objects.filter(olevba__icontains=search_term).order_by('created').distinct()
            search_type="olevba"
        if mod == "rtf":
            search_res = URL.objects.filter(rtfobj__icontains=search_term).order_by('created').distinct()
            search_type="rtfobj"
        '''
            
    else:
        #Do search without special modifier tag - searches MD5s
        search_res = URL.objects.filter(uri__icontains=search_term).order_by('created').distinct()
        search_type = "uri"

    
        
    if search_res:
    
        final_results = {'field': search_type,
                         'debug': [],
                         'hits': [],}  #Build the final_results dict to return the search results we're looking for
    
        for s in search_res:
            found_field_data = getattr(s, search_type)
            if found_field_data:
                final_results['debug'].append(str(found_field_data))
            pre_pattern = "(.*" + search_term + ".*)"
            pattern = re.compile(pre_pattern, re.I)
            m = pattern.search(str(found_field_data))
            if m:
                final_results['hits'].append({'uri': s.uri,
                                              'md5': s.md5,
                                              'result': m.group(1)},)
    else:
        final_results = "No results found."
        
#    return search_res, search_type
    return final_results