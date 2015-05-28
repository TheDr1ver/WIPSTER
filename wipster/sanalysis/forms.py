from django import forms
from .models import Sample

class UploadFileForm(forms.ModelForm):


    class Meta:

#class UploadFileForm(forms.Form):
        model = Sample
        #fields = '__all__'
        fields = ('sample', 'ticket')
#    ticket = forms.CharField(max_length=32)
#    sample = forms.FileField()
