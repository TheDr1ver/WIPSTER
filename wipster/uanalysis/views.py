from django.http import HttpResponseRedirect
from django.shortcuts import render, render_to_response, get_object_or_404
from django.utils import timezone
from uanalysis.settings import *
from .forms import UploadUrlForm
from .models import URL
import handler, hashlib

# Create your views here.
def upload_form(request):

    
    if request.method == 'POST':
        form = UploadUrlForm(request.POST)
        if form.is_valid():

            uri = request.POST['uri']
            newurl = URL(
                uri = uri,
                ticket = request.POST['ticket'],
                md5 = hashlib.md5(uri).hexdigest(),
                #html = handler.get_html(uri),
            )
            ua = request.POST['UserAgent']
            results = handler.get_thug(uri, ua)
            newurl.html = unicode(results['html'], 'utf-8', errors="replace")
            newurl.thug = unicode(results['thug_res'], 'utf-8', errors="replace")
            newurl.js = unicode(results['js'], 'utf-8', errors="replace")
            newurl.js_didier = unicode(results['js_didier'], 'utf-8', errors="replace")
            
            #newurl.js = handler.get_js(newurl.html)
        
            #If VirusTotal is activated, get vt results
            #URL['vt']=handler.get_vt(url)
            
            newurl.save()

            newpage = "/uanalysis/url/" + newurl.md5

            return HttpResponseRedirect(newpage)


    else:
        form = UploadUrlForm()
        url = URL.objects.filter(created__lte=timezone.now()).order_by('-id')[:25]
        return render(request, 'uanalysis/upload_form.html', {'form': form, 'url': url})

#    return render_to_response('sanalysis/upload_form.html', {'form': form})

    
def url_page(request,md5):
    #a=1
    url = URL.objects.filter(md5=md5)
    
    #Format/highlight interesting strings
    url[len(url)-1].thug = handler.get_formatting(url[len(url)-1].thug, 'thug')
    url[len(url)-1].html = handler.get_formatting(url[len(url)-1].html, 'url')
    url[len(url)-1].html = handler.get_formatting(url[len(url)-1].html, 'form_post')
    url[len(url)-1].js = handler.get_formatting(url[len(url)-1].js, 'url')
    url[len(url)-1].js_didier = handler.get_formatting(url[len(url)-1].js_didier, 'url')
    return render(request, 'uanalysis/url_page.html', {'url': url})
    
def search_form(request):
    a=1