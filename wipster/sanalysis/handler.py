# This file is meant to manipulate the uploaded file and
# add important information to the database

import hashlib, os, string, time, magic, pydeep, exiftool, subprocess
import re, sys, binascii
#from django.utils import timezone

from sanalysis.settings import *

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
    
    return os.path.join('sanalysis', 'samples', h, basename.lower() + ext.lower())

def get_exif(f):

    fullpath = get_fullpath(f)
    file = fullpath
    with exiftool.ExifTool() as et:
        metadata = et.execute(f.name)
    
    return metadata

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

    return string_res

def get_balbuzard(f):
    # Call Balbuzard.py - location set in settings.py
    run = subprocess.Popen(["python",balbuzard_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    balbuzard_res = run.communicate()[0]

    return balbuzard_res

def get_trid(f):
    # Call TRiD - location set in settings.py
    run = subprocess.Popen([trid_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    trid_res = run.communicate()[0]

    return trid_res

def get_peframe(f):
    # Call peframe (only if executable file detected from python-magic)
    run = subprocess.Popen([peframe_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    peframe_res = run.communicate()[0]

    return peframe_res

def get_pescanner(f):
    # Call pescanner (only if EXE detected)
    run = subprocess.Popen([pescanner_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    pescanner_res = run.communicate()[0]

    return pescanner_res

def get_pdfid(f):
    # Call PDFiD (only if PDF detected)
    run = subprocess.Popen([pdfid_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    pdfid_res = run.communicate()[0]

    return pdfid_res

def get_peepdf(f):
    # Call PEEPDF (only if PDF detected)
    run = subprocess.Popen([peepdf_loc,"-g",f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    peepdf_res = run.communicate()[0]

    return peepdf_res

def get_oleid(f):
    # Call OLEID (only if Word Doc detected)
    run = subprocess.Popen([oleid_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    oleid_res = run.communicate()[0]

    return oleid_res

def get_olemeta(f):
    # Call OLEMETA (only if Word Doc and OLE identified)
    run = subprocess.Popen([olemeta_loc,f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    olemeta_res = run.communicate()[0]

    return olemeta_res

def get_olevba(f):
    # Call OLEVBA (only if Word Doc and VBA identified)
    run = subprocess.Popen([olevba_loc,"--decode",f.name],
                           stdout=subprocess.PIPE,
                           stdin=subprocess.PIPE)

    olevba_res = run.communicate()[0]

    return olevba_res

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

    for index, data in rtf_iter_objects(f.name):
        rtfobj_res += 'found object size %d at index %08X \r\n' % (len(data), index)
        fname, junk = os.path.split(f.name)
        fname += '/object_%08X.bin' % index
        rtfobj_res += 'saving to file %s \r\n' %fname
        open(fname, 'wb').write(data)

    return rtfobj_res
