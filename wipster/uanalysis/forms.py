from django import forms
from .models import URL

class UploadUrlForm(forms.ModelForm):

    UserAgent = forms.ChoiceField(choices=[('Internet Explorer 6.0 (Windows XP)', u'Internet Explorer 6.0 (Windows XP)'),
                                    ('Internet Explorer 6.1 (Windows XP)', u'Internet Explorer 6.1 (Windows XP)'),
                                    ('Internet Explorer 8.0 (Windows XP)',u'Internet Explorer 8.0 (Windows XP)'),
                                    ('Firefox 12.0 (Windows XP)', u'Firefox 12.0 (Windows XP)'),
                                    ('Chrome 20.0.1132.47 (Windows 7)', u'Chrome 20.0.1132.47 (Windows 7)'),
                                    ('Chrome 25.0.1364.123 (Samsung Galaxy S II, Android 4.0.3)', u'Chrome 25.0.1364.123 (Samsung Galaxy S II, Android 4.0.3)'),
                                    ('Chrome 19.0.1084.54 (MacOS X 10.7.4)',u'Chrome 19.0.1084.54 (MacOS X 10.7.4)'),
                                    ('Safari 8.0 (iPad, iOS 8.0.2)',u'Safari 8.0 (iPad, iOS 8.0.2)')])

    class Meta:

#class UploadFileForm(forms.Form):
        model = URL
        #fields = '__all__'
        fields = ('uri', 'ticket')
        
#    ticket = forms.CharField(max_length=32)
#    sample = forms.FileField()
'''
'Internet Explorer 6.0 (Windows XP)': 'winxpie60',
'Internet Explorer 6.1 (Windows XP)':'winxpie61',
'Internet Explorer 7.0 (Windows XP)':'winxpie70',
'Internet Explorer 8.0 (Windows XP)':'winxpie80',
'Chrome 20.0.1132.47 (Windows XP)':'winxpchrome20',
'Firefox 12.0 (Windows XP)':'winxpfirefox12',
'Safari 5.1.7 (Windows XP)':'winxpsafari5',
'Internet Explorer 6.0 (Windows 2000)':'win2kie60',
'Internet Explorer 8.0 (Windows 2000)':'win2kie80',
'Internet Explorer 8.0 (Windows 7)':'win7ie80',
'Internet Explorer 9.0 (Windows 7)':'win7ie90',
'Chrome 20.0.1132.47 (Windows 7)':'win7chrome20',
'Chrome 40.0.2214.91 (Windows 7)':'win7chrome40',
'Firefox 3.6.13 (Windows 7)':'win7firefox3',
'Safari 5.1.7 (Windows 7)':'win7safari5',
'Safari 5.1.1 (MacOS X 10.7.2)':'osx10safari5',
'Chrome 19.0.1084.54 (MacOS X 10.7.4)':'osx10chrome19',
'Chrome 26.0.1410.19 (Linux)':'linuxchrome26',
'Chrome 30.0.1599.15 (Linux)':'linuxchrome30',
'Firefox 19.0 (Linux)':'linuxfirefox19',
'Chrome 18.0.1025.166 (Samsung Galaxy S II, Android 4.0.3)':'galaxy2chrome18',
'Chrome 25.0.1364.123 (Samsung Galaxy S II, Android 4.0.3)':'galaxy2chrome25',
'Chrome 29.0.1547.59 (Samsung Galaxy S II, Android 4.1.2)':'galaxy2chrome29',
'Chrome 18.0.1025.133 (Google Nexus, Android 4.0.4)':'nexuschrome18',
'Safari 7.0 (iPad, iOS 7.0.4)':'ipadsafari7',
'Safari 8.0 (iPad, iOS 8.0.2)':'ipadsafari8',
'Chrome 33.0.1750.21 (iPad, iOS 7.1)':'ipadchrome33',
'Chrome 35.0.1916.41 (iPad, iOS 7.1.1)':'ipadchrome35',
'Chrome 37.0.2062.52 (iPad, iOS 7.1.2)':'ipadchrome37',
'Chrome 38.0.2125.59 (iPad, iOS 8.0.2)':'ipadchrome38',
'Chrome 39.0.2171.45 (iPad, iOS 8.1.1)':'ipadchrome39'}
'''