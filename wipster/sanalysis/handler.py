# This file is meant to manipulate the uploaded file and
# add important information to the database

import hashlib, os, string, time, magic, pydeep, exiftool, subprocess
import re, sys, binascii, simplejson, urllib, urllib2, json
#from django.utils import timezone

from sanalysis.settings import *
from .models import Sample

def handle_uploaded_file(f):
#    with open('/home/remnux/test.txt', 'wb+') as destination:
#        for chunk in f.chunks():
#            destination.write(chunk)

    '''
    with open('/home/remnux/test.txt', 'wb+') as destination:
        destination.write("\n---------------\nName:\t")
        destination.write(f.name)
        destination.write("\nUploaded:\t")
        destination.write(time.strftime("%c"))
        destination.write("\nSize:\t")
        destination.write(str(f.size))
        destination.write("\nType:\t")
        destination.write(f.content_type)
        destination.write("\nCharset:\t")
        if f.charset:
            destination.write(f.charset)

        fmd5 = hashlib.md5(f.read()).hexdigest()
        fsha1 = hashlib.sha1(f.read()).hexdigest()
        fsha256 = hashlib.sha256(f.read()).hexdigest()

        destination.write("\nMD5:\t")
        destination.write(fmd5)
        destination.write("\nSHA1:\t")
        destination.write(fsha1)
        destination.write("\nSHA256:\t")
        destination.write(fsha256)
    '''
def get_savename(f):

    basename, ext = os.path.splitext(f.filename)
    basename = ''.join(e for e in basename if e.isalnum())
    #basename = basename.decode('unicode_escape').encode('ascii','ignore')   #Strip unicode characters
    #basename = unicode(basename, 'utf-8', errors="ignore")
    return os.path.join(basename.lower() + ext.lower() + '.MAL')
    

def get_filetype(f):

    m=magic.open(magic.MAGIC_NONE)
    m.load()
    buf = f.read(4096)
    position = f.seek(0, 0)
    type=m.buffer(buf)

    return type


def get_md5(f):

    md5 = hashlib.md5(f.read()).hexdigest()
    position = f.seek(0, 0)

    return md5

def get_sha1(f):

    sha1 = hashlib.sha1(f.read()).hexdigest()
    position = f.seek(0, 0)

    return sha1

def get_sha256(f):

    sha256 = hashlib.sha256(f.read()).hexdigest()
    position = f.seek(0, 0)

    return sha256

def get_fuzzy(f):

    fuzzy = pydeep.hash_buf(f.read())
    position = f.seek(0, 0)

    return fuzzy

def get_fullpath(f):

    h = get_md5(f)
    basename, ext = os.path.splitext(f.name)
    basename = ''.join(e for e in basename if e.isalnum())
    #basename = basename.decode('unicode_escape').encode('ascii','ignore')   #Strip unicode characters
    #basename = unicode(basename, 'utf-8', errors="ignore")
    
    return os.path.join('sanalysis', 'samples', h, basename.lower() + ext.lower())

def get_exif(f):

    fullpath = get_fullpath(f)
    file = fullpath
    with exiftool.ExifTool() as et:
        metadata = et.execute(f.name)
    #def_enc = sys.getdefaultencoding()
    return unicode(metadata, 'utf-8', errors="replace")

def get_strings(f):

    string_res = "#### ASCII ####\r\n\r\n"

    run = subprocess.Popen(["strings",f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    string_res += run.communicate()[0]
    string_res += "\r\n#### UNICODE ####\r\n\r\n"

    run = subprocess.Popen(["strings","-e","l",f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    string_res += run.communicate()[0]

    return unicode(string_res, 'utf-8', errors="replace")

def get_balbuzard(f):
    # Call Balbuzard.py - location set in settings.py
    run = subprocess.Popen(["python",balbuzard_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    balbuzard_res = run.communicate()[0]

    return unicode(balbuzard_res, 'utf-8', errors="replace")

def get_trid(f):
    # Call TRiD - location set in settings.py
    run = subprocess.Popen([trid_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    trid_res = run.communicate()[0]

    return unicode(trid_res, 'utf-8', errors="replace")

def get_peframe(f):
    # Call peframe (only if executable file detected from python-magic)
    run = subprocess.Popen([peframe_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    peframe_res = run.communicate()[0]

    return unicode(peframe_res, 'utf-8', errors="replace")

def get_pescanner(f):
    # Call pescanner (only if EXE detected)
    run = subprocess.Popen([pescanner_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    pescanner_res = run.communicate()[0]

    return unicode(pescanner_res, 'utf-8', errors="replace")

def get_pdfid(f):
    # Call PDFiD (only if PDF detected)
    run = subprocess.Popen([pdfid_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    pdfid_res = run.communicate()[0]

    return unicode(pdfid_res, 'utf-8', errors="replace")

def get_peepdf(f):
    # Call PEEPDF (only if PDF detected)
    run = subprocess.Popen([peepdf_loc,"-g",f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    peepdf_res = run.communicate()[0]

    return unicode(peepdf_res, 'utf-8', errors="replace")

def get_oleid(f):
    # Call OLEID (only if Word Doc detected)
    run = subprocess.Popen([oleid_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    oleid_res = run.communicate()[0]

    return unicode(oleid_res, 'utf-8', errors="replace")

def get_olemeta(f):
    # Call OLEMETA (only if Word Doc and OLE identified)
    run = subprocess.Popen([olemeta_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    olemeta_res = run.communicate()[0]

    return unicode(olemeta_res, 'utf-8', errors="replace")

def get_olevba(f):
    # Call OLEVBA (only if Word Doc and VBA identified)
    run = subprocess.Popen([olevba_loc,"--decode",f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    olevba_res = run.communicate()[0]

    return unicode(olevba_res, 'utf-8', errors="replace")

def rtf_iter_objects (filename, min_size=32):
    # From decalage oletools rtfobj script

    PATTERN = r'(?:(?:[0-9A-Fa-f]{2})+\s*)*(?:[0-9A-Fa-f]{2}){4,}'
    TRANSTABLE_NOCHANGE = string.maketrans('', '')

    data = open(filename, 'rb').read()
    for m in re.finditer(PATTERN, data):
        found = m.group(0)
        found = found.translate(TRANSTABLE_NOCHANGE, ' \t\r\n\f\v')
        if len(found)>min_size:
            yield m.start(), found

def get_rtfobj(f):
    # Call RTFOBJ (only if RTF doc identified)
    # Problem: Running like this causes the output to save
    # to the root 'wipster' directory, instead of the
    # appropriate subdirecotry for the sample.
    '''
    run = subprocess.Popen([rtfobj_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    rtfobj_res = run.communicate()[0]

    return rtfobj_res
    '''

    # Code modified from decalage oletools rtfobj script to
    # save in the appropriate directory
    rtfobj_res = ''
    rtflist = []

    for index, data in rtf_iter_objects(f.name):
        rtfobj_res += 'found object size %d at index %08X \r\n' % (len(data), index)
        fname, junk = os.path.split(f.name)
        fname += '/object_%08X.bin' % index
        rtflist.append(fname)
#        rtfobj_res += 'saving to file %s \r\n' %fname
        linkname = fname.split('/', 1)
        rtfobj_res += "saving to file <a href='/{0}'>{1}</a>\r\n".format(linkname[1],fname)
        open(fname, 'wb').write(data)

    #return unicode(rtfobj_res, 'utf-8', errors="replace"), unicode(rtflist, 'utf-8', errors="replace")
    return unicode(rtfobj_res, 'utf-8', errors="replace"), rtflist

def get_rtfobj_str(rtflist):

    rtfobj_str_res = "#### ASCII ####\r\n"

    for o in rtflist:
        run = subprocess.Popen(["strings", o],
                               stdout=subprocess.PIPE,
                               stdin=subprocess.PIPE)

        rtfobj_str_res += run.communicate()[0]

    rtfobj_str_res = "\r\n#### UNICODE #### \r\n"
    for o2 in rtflist:
        run = subprocess.Popen(["strings", "-e", "l", o2],
                               stdout=subprocess.PIPE,
                               stdin=subprocess.PIPE)

        rtfobj_str_res += run.communicate()[0]

    return unicode(rtfobj_str_res, 'utf-8', errors="replace")

def get_rtfobj_balbuz(rtflist):

    rtfobj_balbuz_res = ''

    for o in rtflist:
        run = subprocess.Popen(["python",balbuzard_loc, o],
                               stdout=subprocess.PIPE,
                               stdin=subprocess.PIPE)

        rtfobj_balbuz_res += run.communicate()[0]

    return unicode(rtfobj_balbuz_res, 'utf-8', errors="replace")

def ssdeep_compare(fuzzy, md5):
    #Compare Fuzzy hash of file to all files in db
    #fuzzy_threshold defined in settings.py - default = 10
    all_samples = Sample.objects.all()
    ssdeep_compare_res = ""
    res_dict = {}

    for sample in all_samples:
        if sample.md5 != md5:
            fuzzy_res = pydeep.compare(fuzzy,sample.fuzzy)
            if fuzzy_res >= fuzzy_threshold:
                res_dict[sample.md5] = str(fuzzy_res)

    for k, v in res_dict.iteritems():
        ssdeep_compare_res += "<a href='../"
        ssdeep_compare_res += k
        ssdeep_compare_res += "'>"
        ssdeep_compare_res += k
        ssdeep_compare_res += "</a>\t"
        ssdeep_compare_res += v
        ssdeep_compare_res += "\r\n"


    return ssdeep_compare_res

def get_vt(md5):
    #Query VirusTotal for a given MD5
    url = "https://www.virustotal.com/vtapi/v2/file/report"
    parameters = {"resource": md5,
                  "apikey": vt_key}
    data = urllib.urlencode(parameters)
    req = urllib2.Request(url, data)
    vt_res = ""
    vt_short_res = ""

    if vt_use:
        response = urllib2.urlopen(req)
        json_resp = response.read().decode('utf-8')
        vt_resp = json.loads(json_resp)
        if vt_resp['response_code'] or vt_resp['response_code']==0:
            if vt_resp['response_code']==1:

                #handle json - return long list and short list
        #        for k, v in vt_resp.iteritems():
                text_results = "Results:\t"+str(vt_resp['positives'])+"/"+str(vt_resp['total'])+"\r\n"
                vt_res += text_results
                vt_short_res += text_results
                text_results = "Scan Date:\t"+vt_resp['scan_date']+"\r\n"
                vt_res += text_results
                vt_short_res += text_results
                vt_res += "Permalink:\t"+vt_resp['permalink']+"\r\n\r\n"
                vt_short_res += "<a href='"+vt_resp['permalink']+"' target='_blank'>"
                vt_short_res += vt_resp['permalink']+"</a>\r\n\r\n"
                for vendor, details in vt_resp['scans'].iteritems():
                    spaces = (25 - len(vendor))*" "
                    vt_res += str(vendor)+":"+spaces+str(details['result'])+"\r\n"
                    if vendor in vt_short:
                        vt_short_res += str(vendor)+":\t"+str(details['result'])+"\r\n"
                
            elif vt_resp['response_code']==0:
                vt_res += "No VirusTotal Results Found."
                vt_short_res += vt_res
            else:
                vt_res += "Something went wrong. Response Code: "+str(vt_resp['reponse_code'])
                vt_short_res += vt_res
        else:
            vt_res += "No response code received from VirusTotal. Something is horribly wrong.\r\n"
            vt_res += str(vt_resp)
            vt_short_res += vt_res

    return vt_res, vt_short_res
