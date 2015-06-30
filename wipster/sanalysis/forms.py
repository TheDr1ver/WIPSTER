from django import forms
#from django.core.exceptions import ValidationError
from django.core.validators import validate_integer
from .models import Sample

class UploadFileForm(forms.ModelForm):


    class Meta:

#class UploadFileForm(forms.Form):
        model = Sample
        #fields = '__all__'
        fields = ('sample', 'ticket')
        
    def clean(self):
        cd = self.cleaned_data
        validate_integer(cd.get('ticket', None))
'''        
    def clean_ticket(self):
        ticket = self.cleaned_data['ticket']
        try:
            int(ticket)
            return ticket
        except ValueError:
            raise forms.ValidationError("Ticket must be an integer")
'''        
        
#    ticket = forms.CharField(max_length=32)
#    sample = forms.FileField()
