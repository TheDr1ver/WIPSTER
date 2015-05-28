from django.http import HttpResponseRedirect
from django.shortcuts import render, render_to_response, get_object_or_404
from django.utils import timezone
from .forms import UploadFileForm
from .models import Sample
import hashlib
import handler
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
                s.rtfobj = handler.get_rtfobj(s.sample)

            s.save()


            return HttpResponseRedirect('/sanalysis/')
    else:
        form = UploadFileForm()
        sample = Sample.objects.filter(created__lte=timezone.now()).order_by('created')[:25]
    return render(request, 'sanalysis/upload_form.html', {'form': form, 'sample': sample})
#    return render_to_response('sanalysis/upload_form.html', {'form': form})

def sample_page(request,md5):
#    sample = get_object_or_404(Sample, md5=md5)
    sample = Sample.objects.filter(md5=md5)
    savename = handler.get_savename(sample[0])
    savename = 'samples/'+md5+'/'+savename
    return render(request, 'sanalysis/sample_page.html', {'sample': sample, 'savename': savename})
