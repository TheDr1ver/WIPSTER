from django.http import HttpResponseRedirect
from django.shortcuts import render, render_to_response, get_object_or_404
from django.utils import timezone
from sanalysis.settings import *
from .forms import UploadFileForm
from .models import Sample
import hashlib
import handler, threatanalyzer, crits, search
import re

# Imaginary function to handle an uploaded file.
#from .handler import handle_uploaded_file

def upload_form(request):

    if request.method == 'POST':
        form = UploadFileForm(request.POST, request.FILES)
        if form.is_valid():
#            handle_uploaded_file(request.FILES['file'])
#            newsample = Sample(sample = request.FILES['sample'])
            f = request.FILES['sample']

            
            newsample = Sample(
                sample = f,
                ticket = request.POST['ticket'],
                filename = f.name,
                size = f.size,
#                type = f.content_type,
                type = handler.get_filetype(f),
                md5 = handler.get_md5(f),
                sha1 = handler.get_sha1(f),
                sha256 = handler.get_sha256(f),
                fuzzy = handler.get_fuzzy(f),
            )

            newsample.save()

            #Do post-processing stuff here
            s = Sample.objects.filter().order_by('-id')[0]
            s.exif = handler.get_exif(s.sample)
            s.strings = handler.get_strings(s.sample)
            s.balbuzard = handler.get_balbuzard(s.sample)
            s.trid = handler.get_trid(s.sample)

            #SSDEEP/Fuzzy hash comparison
            s.ssdeep_compare = handler.ssdeep_compare(s.fuzzy, s.md5)

            #VirusTotal Search
            vt_res, vt_short_res = handler.get_vt(s.md5)
            if vt_res:
                s.vt = vt_res
                s.vt_short = vt_short_res

            #If EXE file, run EXE-specific checks
            if "PE32" and "Windows" in s.type:
                s.peframe = handler.get_peframe(s.sample)
                s.pescanner = handler.get_pescanner(s.sample)

            #If PDF file, run PDF-specific checks
            if "PDF" in s.type:
                s.pdfid = handler.get_pdfid(s.sample)
                s.peepdf = handler.get_peepdf(s.sample)

            #If DOC file, run DOC-specific checks
            if "Document File V2" in s.type:
                s.oleid = handler.get_oleid(s.sample)
                #If valid OLE file, run OLEMETA
                olematch = re.compile(r'\|\s+OLE format\s+\|\s+True\s+\|')
                if olematch.search(s.oleid):
                    s.olemeta = handler.get_olemeta(s.sample)
                #If VBA code detected, run OLEVBA
                vbamatch = re.compile(r'\|\s+VBA Macros\s+\|\s+True\s+\|')
                if vbamatch.search(s.oleid):
                    s.olevba = handler.get_olevba(s.sample)

            #If RTF file, run RTFOBJ
            if "Rich Text Format" in s.type:
                rtfobj, rtflist = handler.get_rtfobj(s.sample)
                s.rtfobj = rtfobj

            #If Objects found, run strings/balbuzard against them
            #REMOVED - TOO RESOURCE-INTENSIVE
#            if rtflist:
#                s.rtfobj_str = handler.get_rtfobj_str(rtflist)
#                s.rtfobj_balbuz = handler.get_rtfobj_balbuz(rtflist)
            

            s.save()

            newpage = "/sanalysis/md5/" + s.md5 + "/"

            return HttpResponseRedirect(newpage)


#            return HttpResponseRedirect('/sanalysis/')

#            return render(request, 'sanalysis/sample_page.html', {'sample': sample,
#                                                                  'savename': savename,
#                                                                  'ta_use': ta_use,
#                                                                  'ta_analyses': ta_analyses,
#                                                                  'ta_risks': ta_risks,
#                                                                  'ta_network': ta_network,
#                                                                  'ta_ips': ta_ips,
#                                                                  'ta_domains': ta_domains,
#                                                                  'ta_commands': ta_commands,
#                                                                  'ta_submit': ta_submit,
#                                                                  'crits_use': crits_use,
#                                                                  'crits': crits_dict,
#                                                                  'crits_submit': crits_submit, })


    else:
        form = UploadFileForm()
        sample = Sample.objects.filter(created__lte=timezone.now()).order_by('created')[:25]
        return render(request, 'sanalysis/upload_form.html', {'form': form, 'sample': sample})

#    return render_to_response('sanalysis/upload_form.html', {'form': form})

def sample_page(request,md5):

##################
#### Set Vars ####
##################

#    sample = get_object_or_404(Sample, md5=md5)
    sample = Sample.objects.filter(md5=md5)
    savename = handler.get_savename(sample[0])
    savename = 'samples/'+md5+'/'+savename
    ta_submit = ""

    extra_params = "&md5="+md5


################################################
#### Process ThreatAnalyzer POST Submission ####
################################################

    if request.method=="POST" and ta_use:
        if 'ta_submit' in request.POST:
            if request.POST['ta_submit']:

                ta_submit = threatanalyzer.submit_to_ta(md5, savename)

                current_page = "/sanalysis/md5/"+md5+"/"
                return HttpResponseRedirect(current_page)


#### Process ThreatAnalyzer Data ####

    if ta_use:
        ta_analyses, ta_risks, ta_network, ta_ips, ta_domains, ta_commands = threatanalyzer.get_ta_analyses(md5, extra_params=extra_params)
    else:
        ta_analyses=ta_risks=ta_network = ""
        ta_ips, ta_domains, ta_commands = []

    #Automatically submit to ThreatAnalyzer if ta_autosubmit == True and there is no existing analyses
    if ta_use and ta_autosubmit and "HTTP Error 404" in ta_analyses:
        ta_submit = threatanalyzer.submit_to_ta(md5, savename)    


############################
#### Process CRITs Data ####
############################

    crits_vt = {}
    crits_ta = {}
    crits_dict = {}
    crits_submit = ""

    if crits_use:
        if sample[0].vt_short:
            vt_short_res = sample[0].vt_short
            crits_vt = crits.crits_vt(vt_short_res)
            if not (ta_ips or ta_domains or ta_commands):
                crits_dict = crits_vt

        if ta_use and (ta_ips or ta_domains or ta_commands):
            crits_ta = crits.crits_parse(ta_ips, ta_domains, ta_commands)
            if not sample[0].vt_short:    
                crits_dict = crits_ta

        if crits_vt and crits_ta:
            crits_dict.clear()
            crits_dict = crits_vt.copy()
            crits_dict.update(crits_ta)

#        crits_dict_clean = crits.crits_clean(crits_dict)


    else:
        crits_dict = { 'crits_ips' : [],
                       'crits_domains': [],
                       'crits_uas' : [],
                       'crits_vts' : [],
                       'crits_commands' : [] }

############################
#### Process CRITs Form ####
############################

    if request.method=="POST" and crits_use:
        if 'crits_submit' in request.POST:
            if request.POST['crits_submit']:
                crits_submit = crits.submit_to_crits(request.POST, sample[0], savename=savename)

    #If crits_autosubmit == True, all samples and tickets will be added to CRITs and related to eachother
    if crits_use and crits_autosubmit and request.method!="POST":
        request.POST = {}
        crits_submit = crits.submit_to_crits(request.POST, sample[0], savename=savename)

#            current_page = "/sanalysis/md5/"+md5+"/"
#            return HttpResponseRedirect(current_page)

##########################
#### RENDER MAIN PAGE ####
##########################


    return render(request, 'sanalysis/sample_page.html', {'sample': sample,
                                                          'savename': savename,
                                                          'ta_use': ta_use,
                                                          'ta_analyses': ta_analyses,
                                                          'ta_risks': ta_risks,
                                                          'ta_network': ta_network,
                                                          'ta_ips': ta_ips,
                                                          'ta_domains': ta_domains,
                                                          'ta_commands': ta_commands,
                                                          'ta_submit': ta_submit,
                                                          'crits_use': crits_use,
                                                          'crits': crits_dict,
                                                          'crits_submit': crits_submit, })

##############################                                                          
#### Build Search Routine ####
##############################

search_content = {}

def search_form(request):

    if request.method=="POST":
        if 'search_term' in request.POST:
            #Locate the content
            search_results = search.process_search(request.POST['search_term'])
            #Render the response
            return render(request, 'sanalysis/search.html', {'search_results': search_results})
            
    else:
        return render(request, 'sanalysis/search.html')