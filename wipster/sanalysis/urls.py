from django.conf.urls import include, url
from . import views

urlpatterns = [
    url(r'^$', views.upload_form),
    url(r'^md5/(?P<md5>[a-f0-9]{32})/$', views.sample_page),
]
