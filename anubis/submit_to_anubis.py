#!/usr/bin/env python
################################################################################
# Script for submitting samples to the ANUBIS analysis service.
#
# Last change: 19/October/2009
# Contact: anubis@iseclab.org
#
# Usage: submit_to_anubis.py [options] ANALYSIS_SUBJECT_1 ANALYSIS_SUBJECT_2 ...
#     OR submit_to_anubis.py [options] -r DIRECTORY
#
# Options:
#   -h, --help            show this help message and exit
#   -a ANALYSIS_TYPE, --analysis-type=ANALYSIS_TYPE
#                         specifies the type of ANALYSIS_SUBJECT. One of ['URL',
#                         'FILE']. The default is FILE.
#   -e EMAIL, --email=EMAIL
#                         specifies the recipient of the analysis result. As
#                         soon as the analysis-server has finished processing
#                         the file the generated report will be sent to the
#                         given emailaddress. You can choose to omit this
#                         argument if you do not want to receive an email-
#                         message containg the analysis-result
#   -r, --recursive       recursively submit all samples found
#   -u USER, --user=USER  the name of your user if any
#   -p PASSWORD, --password=PASSWORD
#                         the correct password for your user
#   --ssl                 connect via SSL
#   -f, --force-analysis  force Anubis to rerun the analysis for this
#                         sample even if a cached report exists. (Works only
#                         when the user has sufficient privileges.)
#   --keep-files=KEEP_FILES
#                         specifies which result files too keep
#                         in addition to the profile and the xml-report.
#                         One of ['ALL', 'ANUBIS_LOG', 'NORMAL']. default is
#                         NORMAL. Requires
#                         sufficient user privileges.
#   --anubis-date=ANUBIS_DATE
#                         changes the date in the virtual environment where the
#                         binary to the specified date. Format: YYYY-MM-DD.
#                         Requires sufficient user privileges.
#   --timeout=TIMEOUT     specifies a different timeout value. Requires
#                         sufficient user privileges.
#
#
# ANALYSIS_SUBJECT_1/ANALYSIS_SUBJECT_2/...: 
#             Depending on the analysis-type parameter these arguments will
#             be interpreted as the name of a file that shall be uploaded to 
#             anubis or an URL that shall be analyzed by Anubis.
#             In case of analysis_type='FILE' it specifies the relative or 
#             absolute path to the file that will be
#             sent to the analysis-server.
#             If - is given for the filename the file is read from stdin.
#             Note: On Windows you have start the python interpreter with -u
#                   in order to have a stdin-stream in binary mode.
#
# The script returns 0 for successful submissions and values > 0 in case of
# an error.
#
# Example: python ./submit_to_anubis.py --email joe@example.com testfile.exe
#
################################################################################
SECLAB_URL = "http://anubis.iseclab.org/submit.php" 
SECLAB_RESULT_URL = "http://anubis.iseclab.org/?action=result&"

import sys
import optparse
import os
import time
import httplib, urllib, urlparse
from email.MIMEText import MIMEText
from email.MIMEMultipart import MIMEMultipart
from email import Encoders
from email.MIMEBase import MIMEBase
    
# the number of sucessfully submitted samples
num_success = 0
# the number of samples that we failed to submit
num_failed = 0
# stores the parsed command-line arguments
cmdline = None


# This function was copied from an email of Trent Mick and
# afterwards modified
def httprequest(url, postdata={}, headers=None, ssl = False):
    """A urllib.urlopen() replacement for http://... that gets the
    content-type right for multipart POST requests.

    "url" is the http URL to open.
    "postdata" is a dictionary describing data to post. If the dict is
        empty (the default) a GET request is made, otherwise a POST
        request is made. Each postdata item maps a string name to
        either:
        - a string value; or
        - a file part specification of the form:
            {"filename": <filename>,    # file to load content from
             "content": <content>,      # (optional) file content
             "headers": <headers>}      # (optional) headers
          <filename> is used to load the content (can be overridden by
          <content>) and as the filename to report in the request.
          <headers> is a dictionary of headers to use for the part.
          Note: currently the file part content but be US-ASCII text.
    "headers" is an optional dictionary of headers to send with the
        request. Note that the "Content-Type" and "Content-Length"
        headers are automatically determined.

    The current urllib.urlopen() *always* uses:
        Content-Type: application/x-www-form-urlencoded
    for POST requests. This is incorrect if the postdata includes a file
    to upload. If a file is to be posted the post data is:
        Content-Type: multipart/form-data
    
    This returns the response content if the request was successfull
    (HTTP code 200). Otherwise an IOError is raised.

    For example, this invocation:
        url = 'http://www.perl.org/survey.cgi'
        postdata = {
            "name": "Gisle Aas",
            "email": "gisle at aas.no",
            "gender": "M",
            "born": "1964",
            "init": {"filename": "~/.profile"},
        }
   
    Inspiration: Perl's HTTP::Request module.
    http://aspn.activestate.com/ASPN/Reference/Products/ActivePerl/site/lib/HTTP/Request/Common.html
    """    
    if not url.startswith("http://"):
        raise "Invalid URL, only http:// URLs are allow: url='%s'" % url

    if not headers:
        headers = {}
    
    if not postdata:
        method = "GET"
        body = None
    else:
        method = "POST"

        # Determine if require a multipart content-type: 'contentType'.
        for part in postdata.values():
            if isinstance(part, dict):
                contentType = "multipart/form-data"
                break
        else:
            contentType = "application/x-www-form-urlencoded"
        headers["Content-Type"] = contentType

        # Encode the post data: 'body'.
        if contentType == "application/x-www-form-urlencoded":
            body = urllib.urlencode(postdata)
        elif contentType == "multipart/form-data":
            message = MIMEMultipart(_subtype="form-data")
            for name, value in postdata.items():
                if isinstance(value, dict):
                    # Get content.
                    if "content" in value:
                        content = value["content"]
                    else:
                        fp = open(value["filename"], "rb")
                        content = fp.read()

                    part = MIMEBase('application', "octet-stream")
                    part.set_payload( content )
#                    Encoders.encode_base64(part)

                    # Add content-disposition header.
                    dispHeaders = value.get("headers", {})
                    if "Content-Disposition" not in dispHeaders:
                        #XXX Should be a case-INsensitive check.
                        part.add_header("Content-Disposition", "form-data",
                                        name=name, filename=value["filename"])
                    for dhName, dhValue in dispHeaders:
                        part.add_header(dhName, dhValue)
                else:
                    # Do not use ctor to set payload to avoid adding a
                    # trailing newline.
                    part = MIMEText(None)
                    part.set_payload(value, "us-ascii")
                    part.add_header("Content-Disposition", "form-data",
                                    name=name)
                message.attach(part)
            message.epilogue = "" # Make sure body ends with a newline.
            # Split off the headers block from the .as_string() to get
            # just the message content. Also add the multipart Message's
            # headers (mainly to get the Content-Type header _with_ the
            # boundary attribute).
            headerBlock, body = message.as_string().split("\n\n",1)
            for hName, hValue in message.items():
                headers[hName] = hValue
            #print "XXX ~~~~~~~~~~~~ multi-part body ~~~~~~~~~~~~~~~~~~~"
            #import sys
            #sys.stdout.write(body)
            #print "XXX ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
        else:
            raise "Invalid content-type: '%s'" % contentType

    # Make the HTTP request and get the response.
    # Precondition: 'url', 'method', 'headers', 'body' are all setup properly.
    scheme, netloc, path, parameters, query, fragment = urlparse.urlparse(url)
    if parameters or query or fragment:
        raise "Unexpected URL form: parameters, query or fragment parts "\
              "are not allowed: parameters=%r, query=%r, fragment=%r"\
              % (parameters, query, fragment)

    if ssl:
        conn = httplib.HTTPSConnection(netloc)
    else:
        conn = httplib.HTTPConnection(netloc)
    conn.request(method, path, body, headers)
    response = conn.getresponse()
    return response


def submit(analysis_type, analysis_subject, aux_files = [],
           email_addr = None, user_name = None, user_password = None, 
           force_analysis = False, keep_files = None, 
           anubis_date = None, timeout = None, ssl = False, dump_process = False):
    """
    Submits the 'analysis_subject' to ANUBIS.
    Returns the task_id of the created task.
    """
    if len(aux_files) > 0:
        assert(analysis_type == "file")

    def get_anubis_error_msg(http_page):
        lines = http_page.splitlines()

        have_h1 = False
        start_ind = 0
        end_ind = 0
        for line_no, line in enumerate(lines):
            if line == '<h1 class="bodystart">Fatal Submission Error</h1>':
                have_h1 = True
            elif have_h1 and line == "<p>":
                start_ind = line_no + 1
            elif start_ind > 0 and line == "</p>":
                end_ind = line_no - 1

        error_msg = http_page
        if start_ind > 0 and end_ind >= start_ind and end_ind < len(lines):
            error_msg = "\n".join(lines[start_ind:end_ind+1])

        # show the first 500 characters
        return error_msg[:500]


    try:
        post_data = {}

        if analysis_type == "FILE":
            if (analysis_subject == "-"):
                content = sys.stdin.read()
                fn = "stdin"
            else:
                content = open(analysis_subject, "rb").read()
                fn = analysis_subject

            post_data['analysisType'] = 'file'
            post_data["executable"] = {"content" : content, "filename" : fn} 
        elif analysis_type == "URL":
            post_data['analysisType'] = 'url'
            post_data['url'] = analysis_subject
        else:
            assert(False)

        for c, af in enumerate(aux_files):
            content = open(af, "rb").read()
            post_data["aux_file[%s]" % c] = {"content" : content, "filename" : af}

        if (email_addr):
            post_data["notification"] = "email"
            post_data["email"] = email_addr
        else:
            post_data["notification"] = "browser"
            post_data["email"] = ""

        if (user_name):
            post_data["username"] = user_name
            post_data["password"] = user_password

        if force_analysis:
            post_data["force_analysis"] = "on"
        if anubis_date:
            post_data["anubis_date"] = anubis_date
        if timeout:
            post_data['timeout'] = str(timeout)
        if keep_files:
            post_data['keep_files_level'] = keep_files.lower()
        if dump_process:
            post_data["dump_process"] = "on"

        response = httprequest(SECLAB_URL, post_data, ssl=ssl)
        if response.status == 200 and response.getheader("taskid"):
            return response.getheader('taskid')

        error_code = response.getheader("AnubisAPI.error.result")
        if not error_code:
            # legacy code - do our best to find a reasonable explanation
            # of what happened
            error_code = get_anubis_error_msg(response.read())
        
        print "Error submitting analysis subject '%s'." % (analysis_subject)
        print "The anubis server replied: %s" % (error_code)

    except IOError:
        print "File does not exist!"
    except Exception:
        import traceback
        traceback.print_exc()

    return None


def submit_dir(directory,
               email_addr = None, user_name = None, user_password = None, 
               force_analysis = False, keep_files = None, 
               anubis_date = None, timeout = None, ssl = False, dump_process = False):

    """Submits all files found in the directory 'd' to Anubis.
    """
    global num_failed
    global num_success

    for entry in os.listdir(directory):
        full = os.path.join(directory, entry)
        if os.path.isdir(full):
            submit_dir(full,
                       email_addr, user_name, user_password,
                       force_analysis, keep_files,
                       anubis_date, timeout, ssl, dump_process)
        else:
            if (submit('FILE',
                       full, [],
                       email_addr, user_name, 
                       user_password, force_analysis,
                       keep_files, anubis_date,
                       timeout, ssl, dump_process)):
                num_success += 1
            else:
                num_failed += 1
                

def main():
    global num_failed
    global num_success
    global cmdline

    usage_msg="%prog [options] ANALYSIS_SUBJECT_1 ANALYSIS_SUBJECT_2 ..."
    usage_msg+= "\n    OR %prog [options] -r DIRECTORY"
    parser = optparse.OptionParser(usage=usage_msg)
    analysis_type_list = ["URL", "FILE"]
    parser.add_option("-a","--analysis-type", dest="analysis_type", type="choice",
                      choices = analysis_type_list, default="FILE",
                      help = "specifies the type of ANALYSIS_SUBJECT. One of %s"
                      % analysis_type_list +
                      ". The default is %default." )
    parser.add_option("-e", "--email", dest="email", type="string", 
                      help="specifies the recipient of the analysis result. As "+
                      "soon as the analysis-server has finished processing the "+
                      "file the generated report will be sent to the given "+
                      "emailaddress. You can "+
                      "choose to omit this argument if you do not want to receive "+
                      "an email-message containg the analysis-result")
    parser.add_option("-r", "--recursive", dest="recursive", action="store_true",
                      default=False, help = "recursively submit all samples found")
    parser.add_option("-u", "--user", dest="user", type="string", 
                      help="the name of your user if any")
    parser.add_option("-p", "--password", dest="password", type="string", 
                      help="the correct password for your user")
    parser.add_option("--ssl", dest="ssl", action="store_true", 
                      default = False,
                      help="connect via SSL")

    # all of the following options, require a valid Anubis user 
    # with sufficient privileges
    parser.add_option("-f", "--force-analysis", dest="force_analysis",
                      action="store_true",
                      default = False,
                      help="force Anubis to rerun the analysis for this \
                      sample even if a cached report exists. (Works only \
                      when the user has sufficient privileges.)")

    keep_files_list = ["ALL", "ANUBIS_LOG", "NORMAL"]
    parser.add_option("--keep-files", dest="keep_files", type="choice", 
                      choices= keep_files_list, default="NORMAL",
                      help="specifies which result files too keep \
                            in addition to the profile and the XML-report.\
                            One of %s. default is %%default. Requires \
                            sufficient user privileges." % keep_files_list)
    parser.add_option("--anubis-date", dest="anubis_date", type="string",
                      help="changes the date in the virtual environment "+
                      "where the binary to the specified date. " +
                      "Format: YYYY-MM-DD. " +
                      "Requires sufficient user privileges.")
    parser.add_option("--timeout", dest="timeout", type="int",
                      help="specifies a different timeout value. " + 
                            "Requires sufficient user privileges.")
    parser.add_option("--dump-process", dest="dump_process", action="store_true",
                      default = False,
                      help="dump 'unpacked' version of analyzed processes" + 
                            "Requires sufficient user privileges.")

    empty_options = parser.parse_args()[0]
    
    (cmdline, args) = parser.parse_args()

    if (len(args) == 0):
        parser.print_help()
        sys.exit(2)

    if cmdline.anubis_date:
        try:
            time.strptime(cmdline.anubis_date, "%Y-%m-%d")
        except ValueError:
            print "The supplied anubis_date '%s' is not a valid date. " % \
                cmdline.anubis_date
            print "Please specify the date in the format 'YYYY-MM-DD'."
            return

    for ana_subj in args:
        if cmdline.recursive and os.path.isdir(ana_subj):
            if cmdline.analysis_type != 'FILE':
                print "Error: The option '--recursive' can only be used " +\
                    "together with an analysis subject of type file."
                sys.exit(1)

            submit_dir(ana_subj,
                       cmdline.email, cmdline.user, 
                       cmdline.password, cmdline.force_analysis,
                       cmdline.keep_files, cmdline.anubis_date,
                       cmdline.timeout, cmdline.ssl,cmdline.dump_process)
        else:
            task_id = submit(cmdline.analysis_type,
                             ana_subj, [],
                             cmdline.email, cmdline.user, 
                             cmdline.password, cmdline.force_analysis,
                             cmdline.keep_files, cmdline.anubis_date,
                             cmdline.timeout, cmdline.ssl, cmdline.dump_process)
            if (task_id):
                print "Get the result at %stask_id=%s" % (SECLAB_RESULT_URL, task_id)
                num_success += 1
            else:
                num_failed +=1
                
    if num_success:
        print "Successfully submitted %d analysis subjects." % num_success
    if num_failed:
        print "Failed to submit %d analysis subjects." % num_failed
    
           
if __name__ == '__main__':
    main()
