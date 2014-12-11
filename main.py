#############################################
##       MAIN HANDLER FILE FOR VIEWS       ##
#############################################
import os
import cgi
import config
import ee
import jinja2
import webapp2
import datetime
import numpy
import json
import httplib2

#from forms import *
import forms
import collectionMethods
from google.appengine.api import urlfetch
urlfetch.set_default_fetch_deadline(60)
httplib2.Http(timeout=15)

#############################################
##       SET DIRECTORY FOR PAGES          ##
#############################################
template_dir = os.path.join(os.path.dirname(__file__),'templates')
JINJA_ENVIRONMENT= jinja2.Environment(autoescape=True,
    loader=jinja2.FileSystemLoader(template_dir))

#############################################
##       HOME PAGE                         ##
#############################################
class MainPage(webapp2.RequestHandler):
    def get(self):
        template = JINJA_ENVIRONMENT.get_template('home.html')
        self.response.out.write(template.render({}))

#############################################
##       DROUGHT TOOL PAGE                 ##
#############################################
class DroughtTool(webapp2.RequestHandler):
    def set_form_params(self):
        #sets form  parameters
        self.ppost = 0
        self.form_error = {}
        self.opacity = self.request.get('opacity',str(14*0.05))
        self.variable = self.request.get('basicvariable','pr')
        self.domainType = self.request.get('domainType','full')
        self.state = self.request.get('state','California')
        self.anomOrValue = self.request.get('anomOrValue','anom')
        #self.pointLatLong = self.request.get('pointLatLong','-112,42')
        #self.pointLatLongX = self.pointLatLong.split(",")
        #self.pointLong = float(self.pointLatLongX[0])
        #self.pointLat = float(self.pointLatLongX[1])
        self.dateStart = self.request.get('dateStart','2014-06-01')
        self.dateEnd = self.request.get('dateEnd','2014-08-31')
        self.pointsLongLat = self.request.get('pointsLongLat','-112,42')

        self.opacity = self.request.get('opacity',str(14*0.05))
        self.NELat = self.request.get('NELat',45)
        self.NELong= self.request.get('NELong',-95)
        self.SWLat= self.request.get('SWLat',40)
        self.SWLong= self.request.get('SWLong',-111)
        self.kmloption = self.request.get('kmloption', '')
        self.kmlurl = self.request.get('kmlurl', '')

        self.minColorbar = self.request.get('minColorbar', None)
        self.maxColorbar = self.request.get('maxColorbar', None)
        self.palette = self.request.get('palette', None)
        self.colorbarmap = self.request.get('colorbarmap', 'GnBu')
        self.colorbarsize = self.request.get('colorbarsize', '8')

	if(self.domainType=='full'):
            mz= 5;
        elif(self.domainType=='states'):
            mz= 6;
	else:
	    mz=4;
        self.mapzoom = self.request.get('mapzoom',mz)

        if self.minColorbar is None and self.maxColorbar is None:
            self.palette,self.minColorbar,self.maxColorbar,self.colorbarLabel=collectionMethods.get_colorbar(self.variable,self.anomOrValue)

    def set_share_link(self, initial_template_values):
        shareLink = 'khegewisch-test.appspot.com?'
        for key, val in initial_template_values.iteritems():
            if str(key[0:4]) == 'form':
                continue
            if key == 'ppost':continue
            #FIX ME: would be nice to not have to do this
            #ie. change basicvariable form name to variable
            if str(key) == 'variable':
                param_str = 'basicvariable' + '=' + str(val)
            else:
                param_str = str(key) + '=' + str(val)
            if shareLink[-1] =='?':
                shareLink+=param_str
            else:
                shareLink+='&' + param_str
        return shareLink

    def set_initial_template_values(self):
        template_values = {
            'form_error': self.form_error,
            'opacity': self.opacity,
            'pointsLongLat':self.pointsLongLat,
            'NELat': self.NELat,
            'NELong': self.NELong,
            'SWLat': self.SWLat,
            'SWLong': self.SWLong,
            'ppost': self.ppost,
            'mapzoom': self.mapzoom,
            'variable': self.variable,
            'state': self.state,
            'domainType': self.domainType,
            'anomOrValue': self.anomOrValue,
            'dateStart': self.dateStart,
            'dateEnd': self.dateEnd,
            'anomOrValue': self.anomOrValue,
            'formPaletteMap': forms.formPaletteMap,
            'formPaletteSize': forms.formPaletteSize,
            'formOpacity': forms.formOpacity,
            'formAnomOrValue': forms.formAnomOrValue,
            'formVariableGrid': forms.formVariableGrid,
            'formLocation': forms.formLocation,
            'formVariableLandsat': forms.formVariableLandsat,
            'formStates': forms.formStates,
            'kmlurl': self.kmlurl,
            'kmloption': self.kmloption,
            'palette': self.palette,
            'minColorbar': self.minColorbar,
            'maxColorbar': self.maxColorbar,
            'colorbarmap': self.colorbarmap,
            'colorbarsize': self.colorbarsize,
        }
        if self.colorbarmap:
            template_values['colorbarmap']= self.colorbarmap
        if self.colorbarsize:
            template_values['colorbarsize']= self.colorbarsize
        if self.palette:
            template_values['palette']= self.palette
        if self.minColorbar:
            template_values['minColorbar']= self.minColorbar
        if self.maxColorbar:
            template_values['maxColorbar']= self.maxColorbar
        template_values['shareLink'] = self.set_share_link(template_values)
        if self.minColorbar:
            template_values['minColorbar'] = self.minColorbar
        if self.maxColorbar:
            template_values['maxColorbar'] = self.maxColorbar
        if self.kmlurl:
            template_values['kmlurl'] = self.kmlurl
        if self.kmloption:
            template_values['kmloption'] = self.kmloption
        #format template values to allow for differnet date formats etc...
        #See format_ functions in forms.py
        formatted_template_values = {}
        for key, val in template_values.iteritems():
            format_function_name = 'format_' + key
            try:
                format_function = getattr(forms,format_function_name)
            except:
                format_function = None

            if format_function:
                formatted_template_values[key] = format_function(val)
            else:
                formatted_template_values[key] = val
        return formatted_template_values

    def check_user_input(self, template_values):
        #Checks for errors in user input
        #See check_ functions in forms.py
        #At first error encountered, spits out error message and exits
        err = None; fieldID = None
        for key, val in template_values.iteritems():
            #do not check form items
            if key[0:4] == 'form':
                continue
            check_function_name = 'check_' + key
            try:
                #See if a check function exists in forms.py
                #If so, executed to check for form errors
                check_function = getattr(forms,check_function_name)
            except:
                continue
            err = check_function(val)
            if err:
                fieldID = key
                return fieldID,err
        return fieldID,err
    #############################################
    ##      GET                                ##
    #############################################
    def get(self):
        ppost=0
        ee.Initialize(config.EE_CREDENTIALS, config.EE_URL)

        #initialize forms
        self.set_form_params()
        template_values = self.set_initial_template_values()

        #Check user input for errors:
        fieldID,input_err = self.check_user_input(template_values)
        if not input_err:
            if self.request.arguments():
                template_values = collectionMethods.get_images(template_values)
        else:
            template_values['form_error'] = {fieldID:input_err}

        template = JINJA_ENVIRONMENT.get_template('droughttool.php')
        self.response.out.write(template.render(template_values))
    #############################################
    ##      POST                                ##
    #############################################
    def post(self):
        ee.Initialize(config.EE_CREDENTIALS, config.EE_URL)
        self.set_form_params()
        template_values = self.set_initial_template_values()
        #Check user input for errors:
        fieldID,input_err = self.check_user_input(template_values)
        if not input_err:
            #Override ppost default
            template_values['ppost'] = 1
            #get the collection and update template values
            template_values = collectionMethods.get_images(template_values)
        else:
            #write error message to html
            template_values['form_error'] = {fieldID:input_err}
        template = JINJA_ENVIRONMENT.get_template('droughttool.php')
        self.response.out.write(template.render(template_values))

#############################################
##       CONTACT PAGE                      ##
#############################################
#class ContactPage(webapp2.RequestHandler):
#	def get(self):
#		template = JINJA_ENVIRONMENT.get_template('contact.html')
#		self.response.out.write(template.render({}))

#############################################
##       ABOUT DATA PAGE                   ##
#############################################
#class DataPage(webapp2.RequestHandler):
#	def get(self):
#		template = JINJA_ENVIRONMENT.get_template('aboutdata.html')
#		self.response.out.write(template.render({}))

#############################################
##       ABOUT METRIC PAGE                 ##
#############################################
#class MetricsPage(webapp2.RequestHandler):
#	def get(self):
#		template = JINJA_ENVIRONMENT.get_template('aboutmetrics.html')
#		self.response.out.write(template.render({}))

#############################################
##       URL MAPPING                        ##
#############################################
app = webapp2.WSGIApplication(
    [#('/', MainPage),
    ('/', DroughtTool),
    #('/droughttool', DroughtTool),
    #('/droughttool/', DroughtTool),
    #('/contact',ContactPage),
    #('/aboutdata',DataPage),
    #('/aboutmetrics',MetricsPage)
],
debug=True)
