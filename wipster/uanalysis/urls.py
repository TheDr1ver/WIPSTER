from django.conf.urls import include, url
from . import views

urlpatterns = [
    url(r'^$', views.upload_form),
    url(r'^url/(?P<md5>[a-f0-9]{32})/$', views.url_page),
    url(r'^search/', views.search_form),
]
