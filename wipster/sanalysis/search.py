from .models import Sample
import re

# Process Search Functions
def process_search(search_term):
    #search_res = Sample.objects.filter().order_by('-id')[0]
    #sample = Sample.objects.filter(created__lte=timezone.now()).order_by('created')[:25]

    search_split = search_term.split("::")
    
    if len(search_split)>1:
        #search_split[0] = modifier, sha1, filename, etc
        #search_split[1] = actual search
        mod = search_split[0]
        search_term = search_split[1]
        if mod == "sha1":
            search_res = Sample.objects.filter(sha1__iexact=search_term).order_by('created').distinct()
            search_type = "sha1"
        if mod == "sha256":
            search_res = Sample.objects.filter(sha256__iexact=search_term).order_by('created').distinct()
            search_type = "sha256"
        if mod == "file":
            search_res = Sample.objects.filter(filename__icontains=search_term).order_by('created').distinct()
            search_type = "filename"
#        if mod == "date":
#            search_res = Sample.objects.filter(date=search_term).order_by('created').distinct()
#            search_type = "date"
        if mod == "ticket":
            search_res = Sample.objects.filter(ticket__iexact=search_term).order_by('created').distinct()
            search_type = "ticket"
        if mod == "vt":
            search_res = Sample.objects.filter(vt__icontains=search_term).order_by('created').distinct()
            search_type = "vt"
        if mod == "exif":
            search_res = Sample.objects.filter(exif__icontains=search_term).order_by('created').distinct()
            search_type = "exif"
        if mod == "string":
            search_res = Sample.objects.filter(strings__icontains=search_term).order_by('created').distinct()
            search_type = "strings"
        if mod == "balbuz":
            search_res = Sample.objects.filter(balbuzard__icontains=search_term).order_by('created').distinct()
            search_type = "balbuzard"
        if mod == "olemeta":
            search_res = Sample.objects.filter(olemeta__icontains=search_term).order_by('created').distinct()
            search_type="olemeta"
        if mod == "vba":
            search_res = Sample.objects.filter(olevba__icontains=search_term).order_by('created').distinct()
            search_type="olevba"
        if mod == "rtf":
            search_res = Sample.objects.filter(rtfobj__icontains=search_term).order_by('created').distinct()
            search_type="rtfobj"
            
    else:
        #Do search without special modifier tag - searches MD5s
        search_res = Sample.objects.filter(md5__iexact=search_term).order_by('created').distinct()
        search_type = "md5"

    
        
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
                final_results['hits'].append({'md5': s.md5,
                                              'filename': s.filename,
                                              'result': m.group(1)},)
    else:
        final_results = "No results found."
        
#    return search_res, search_type
    return final_results