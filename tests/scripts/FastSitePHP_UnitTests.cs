using System;
using System.Collections.Generic;
using System.Text;

namespace FastSitePHP_UnitTests
{
    /// <summary>
    /// C# Source Code for creating an Executable Program for Unit Testing 
    /// the Request Header 'Expect: 100-continue' with FastSitePHP and Redirect
    /// Response Body Text. A separate Shell Script is available for testing 
    /// with Unix/Linux/Mac.
    /// 
    /// Author: Conrad Sollitt
    /// Dates: 2015 - 2017
    /// </summary>
    class Program
    {
        /// <summary>
        /// Single function program, code execution starts here
        /// </summary>
        /// <param name="args"></param>
        static void Main(string[] args)
        {
            try
            {
                // Define the Root URL. This value will need to be changed to 
                // reflect the location where FastSitePHP is installed on either 
                // the local computer or if on a public site the site's URL.
                var rootUrl = "http://localhost:3000/vendor/fastsitephp/tests/";

                // Test URL's along with Form Data to POST.
                var postUrl = "test-web-request.php/post-data-12";
                var redirectHeadUrl = "test-app.php/redirect-filter";
                var redirectUrl = "test-app.php/redirect-{0}";
                var responseRedirectUrl = "test-web-response.php/redirect-{0}";
                var redirectParamsUrl = "test-{0}.php/redirect-with-params";
                var responseParams = "<h1>Found</h1><p>Redirecting to <a href=\"redirected-with-params?param1=abc&amp;param2=123\">redirected-with-params?param1=abc&amp;param2=123</a></p>";
                var formData = Encoding.ASCII.GetBytes("site=FastSitePHP&page=UnitTest12");
                var successCount = 0;

                // Define Test URL's along with the expected text result
                var pages = new Dictionary<string, string>()
                {
                    { postUrl + "?data=Expect100", "Has Expect:100-continue: true" },
                    { postUrl + "?data=post", "POST: [site=FastSitePHP] [page=UnitTest12]" },
                    { postUrl + "?data=input-type", "Input Type: form" },
                    { postUrl + "?data=input", "Input: site=FastSitePHP&page=UnitTest12" },
                    { postUrl, "(form): [site=FastSitePHP] [page=UnitTest12]" },
                    { redirectHeadUrl, "" },
                    { string.Format(redirectParamsUrl, "app"), responseParams },
                    { string.Format(redirectParamsUrl, "web-response"), responseParams }
                };

                var redirectCodes = new Dictionary<int, string>()
                {
                    { 301, "Moved Permanently" },
                    { 302, "Found" },
                    { 303, "See Other" },
                    { 307, "Temporary Redirect" },
                    { 308, "Permanent Redirect" }
                };

                foreach (var redirect in redirectCodes)
                {
                    pages.Add(string.Format(redirectUrl, redirect.Key), string.Format("<h1>{0}</h1><p>Redirecting to <a href=\"redirected-{1}\">redirected-{1}</a></p>", redirect.Value, redirect.Key));
                    pages.Add(string.Format(responseRedirectUrl, redirect.Key), string.Format("<h1>{0}</h1><p>Redirecting to <a href=\"redirected-{1}\">redirected-{1}</a></p>", redirect.Value, redirect.Key));
                }

                // Status
                Console.WriteLine(new String('=', 80));
                Console.WriteLine("Testing URL: " + rootUrl);
                Console.WriteLine(new String('=', 80));

                // Run a Test for Item in the Dictionary
                foreach (var page in pages)
                {
                    // Create a WebRequest Object
                    var request = System.Net.WebRequest.Create(rootUrl + page.Key);

                    // Which URL? If a POST URL send the POST Data.
                    if (page.Key.Contains(postUrl))
                    {
                        // Update Properties to it will POST HTTP Form Data
                        request.Method = "POST";
                        request.ContentType = "application/x-www-form-urlencoded; charset=UTF-8";
                        request.ContentLength = formData.Length;

                        // Write Form Data to the Request Stream.
                        // In C# the using statement is used for IDisposable Objects 
                        // and allows for releasing unmanaged resources without having to 
                        // call a Dispose() function.
                        using (var stream = request.GetRequestStream())
                            stream.Write(formData, 0, formData.Length);
                    }
                    else
                    {
                        // Prevent Request from Redirecting
                        ((System.Net.HttpWebRequest)request).AllowAutoRedirect = false;

                        // Redirct HEAD Test - This should return no content for the Response Body
                        if (page.Key.Contains(redirectHeadUrl))
                            request.Method = "HEAD";
                    }

                    // Read the Response as Text and Check the Result
                    using (var response = request.GetResponse())
                    using (var stream = new System.IO.StreamReader(response.GetResponseStream()))
                    {
                        // Read the result and remove the new-line "\n" character
                        var result = stream.ReadToEnd();
                        result = result.Replace(((char)10).ToString(), "");

                        // Show SUCCESS or ERROR for the Test
                        Console.WriteLine(new String('-', 40));
                        var success = (result == page.Value ? "SUCCESS" : "ERROR");
                        Console.WriteLine(string.Format("{0} [{1} {2}]:", success, request.Method, page.Key));
                        if (success == "SUCCESS")
                            Console.WriteLine('\t' + result);
                        else
                        {
                            Console.WriteLine("\tResult: " + result);
                            Console.WriteLine("\tExpected: " + page.Value);
                        }

                        // Keep count
                        if (success == "SUCCESS")
                            successCount++;
                    }
                }

                // Show the Final Result
                Console.WriteLine(new String('=', 80));
                if (successCount == pages.Count)
                    Console.WriteLine(string.Format("Success all {0} Tests Passed", successCount));
                else
                    Console.WriteLine(string.Format("Error {0} of {1} Tests Passed", successCount, pages.Count));
            }
            catch (Exception e)
            {
                Console.WriteLine("Error running unit tests");
                Console.WriteLine("Check that the variable [rootUrl] is set to a correct value for this computer.");
                Console.WriteLine("Error: " + e.Message);
            }

            // Wait 5 Seconds before existing if running in DEBUG Mode so console output can be seen.
            // This would likely be the case if running this file in Visual Studio; if running from 
            // within Visual Studio and not using a delay the program would terminate as soon as it 
            // completes so seeing console output is not possible.
#if DEBUG
            System.Threading.Thread.Sleep(5000);
#endif
        }
    }
}