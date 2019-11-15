#!/usr/bin/env bash


#*********************************************************************************************
#
# This File is a Unix Shell Script for Unit Testing Features of FastSitePHP that cannot be
# tested with a Web Browser. A separate C# Program is available for testing from Windows.
# By default this script is expected to run on most Mac, Linux, and Unix Computers.
#
# To Run:
# 1) Navigate to the directory of this Script:
#    cd {dir}
# 2) Execute:
#        ./FastSitePHP_UnitTests.sh
#    OR:
#        bash FastSitePHP_UnitTests.sh
#
# Author: Conrad Sollitt
# Dates: 2015 - 2017
#
#*********************************************************************************************


#---------------------------------------------------------
# Main function, settings for the script are defined
# at the top of this function. Only [root_url] needs
# to be changed when testing on different computers.
# New tests can be added to the [tests] Array.
#---------------------------------------------------------
main ()
{
    # The value for [root_url] needs to be changed based on the host or computer to test
    root_url='http://localhost/FastSitePHP/vendor/fastsitephp/tests/'

    # Set Bash script option for extended pattern matching
    # so that strings can be trimmed
    shopt -s extglob

    # Define URLs
    post_url='test-web-request.php/post-data-12'
    redirect_url_app='test-app.php/redirect-'
    redirect_url_res='test-web-response.php/redirect-'
    redirect_url2='test-app.php/redirect-filter'
    params_app_url='test-app.php/redirect-with-params'
    params_res_url='test-web-response.php/redirect-with-params'

    # Define CURL Options
    #
    # Option [-s] is for silent mode and normally not needed when executed from the command line.
    # If not included in Bash then curl outputs info and a progress meter on the response.
    #
    # Options [-F] is for posting form data of type 'multipart/form-data', that
    # specific form type is what triggers a 'Expect: 100-continue' Request Header.
    #
    # Option [-X HEAD] specifies a HEAD Request and [-m] is for max-time in seconds.
    # In most cases a HEAD request with curl would be done instead with option [-I] which
    # shows the Response Headers. However for this script the actual response is being
    # verified and not the headers which is why option [-X] is used. If a [-X HEAD]
    # Request is performed without a timeout then it can take several seconds as CURL
    # will wait if there is no [Content-Length] header.
    #
    curl_post_args='-s -F site=FastSitePHP -F page=UnitTest12'
    curl_get_args='-s'
    curl_head_args='-s -X HEAD -m 1'

    # Keep Count of Tests
    success_count=0
    has_error=0

    # Define an Array of Tests, Bash does not support structs, objects, or multi-dimensional arrays so each
    # Test is packed into a string seperated by vertical bars '|' with extra white space for readability.
    tests=(
        # Check how the Server/Site handles the Request Header 'Expect: 100-continue'
        "${curl_post_args}|  ${post_url}?data=Expect100|   Has Expect:100-continue: true"
        "${curl_post_args}|  ${post_url}?data=post|        POST: [site=FastSitePHP] [page=UnitTest12]"
        "${curl_post_args}|  ${post_url}?data=input-type|  Input Type: form-data"
        "${curl_post_args}|  ${post_url}?data=input|       Input: "
        "${curl_post_args}|  ${post_url}|                  (form-data): [site=FastSitePHP] [page=UnitTest12]"

        # Verify that the contents of [$app->redirect()] output the expected content.
        # A browser will always redirect on these tests so they cannot be unit tested however
        # by default curl does not follow the Response 'Location' header so the content can be checked
        "${curl_get_args}|   ${redirect_url_app}301|       <h1>Moved Permanently</h1><p>Redirecting to <a href=\"redirected-301\">redirected-301</a></p>"
        "${curl_get_args}|   ${redirect_url_app}302|       <h1>Found</h1><p>Redirecting to <a href=\"redirected-302\">redirected-302</a></p>"
        "${curl_get_args}|   ${redirect_url_app}303|       <h1>See Other</h1><p>Redirecting to <a href=\"redirected-303\">redirected-303</a></p>"
        "${curl_get_args}|   ${redirect_url_app}307|       <h1>Temporary Redirect</h1><p>Redirecting to <a href=\"redirected-307\">redirected-307</a></p>"
        "${curl_get_args}|   ${redirect_url_app}308|       <h1>Permanent Redirect</h1><p>Redirecting to <a href=\"redirected-308\">redirected-308</a></p>"

        # Verify that the contents of [$res->redirect()] output the expected content.
        "${curl_get_args}|   ${redirect_url_res}301|       <h1>Moved Permanently</h1><p>Redirecting to <a href=\"redirected-301\">redirected-301</a></p>"
        "${curl_get_args}|   ${redirect_url_res}302|       <h1>Found</h1><p>Redirecting to <a href=\"redirected-302\">redirected-302</a></p>"
        "${curl_get_args}|   ${redirect_url_res}303|       <h1>See Other</h1><p>Redirecting to <a href=\"redirected-303\">redirected-303</a></p>"
        "${curl_get_args}|   ${redirect_url_res}307|       <h1>Temporary Redirect</h1><p>Redirecting to <a href=\"redirected-307\">redirected-307</a></p>"
        "${curl_get_args}|   ${redirect_url_res}308|       <h1>Permanent Redirect</h1><p>Redirecting to <a href=\"redirected-308\">redirected-308</a></p>"

        # Verify that [redirect()] properly escapes URL Parameters in the HTML
        "${curl_get_args}|   ${params_app_url}|            <h1>Found</h1><p>Redirecting to <a href=\"redirected-with-params?param1=abc&amp;param2=123\">redirected-with-params?param1=abc&amp;param2=123</a></p>"
        "${curl_get_args}|   ${params_res_url}|            <h1>Found</h1><p>Redirecting to <a href=\"redirected-with-params?param1=abc&amp;param2=123\">redirected-with-params?param1=abc&amp;param2=123</a></p>"

        # Verify that a HEAD Request using [$app->redirect()] returns no content for the Response Body.
        # If the related lines of code are commented out then this test is expected to still succeed because
        # most Web Servers (Apache, IIS, etc) will likely still strip out the Response Content.
        "${curl_head_args}|  ${redirect_url2}|"
    )

    # Show Root URL
    echo '============================================================='
    echo "Testing with Site: ${root_url}"

    # Loop by array index, in this case numbers from 0 to the length of the array
    for n in "${!tests[@]}"; do
        # Display a separator for each test (this helps for readability and if there is an error)
        echo '-------------------------------------------------------------'

        # Parse the string of test parameters.
        # By default Bash would parse a string on all spaces so change the
        # IFS (Internal Field Separator) to a vertical bar '|' so that the string
        # is parsed into an array of the correct format. Change back IFS afterwords
        # otherwise the curl command would failed below.
        IFS='|'
        a=(${tests[n]})
        curl_args=${a[0]}
        url=${a[1]}
        expected=${a[2]}
        unset IFS

        # Trim leading whitespace, this requires the [shopt -s extglob]
        # option defined above in this script.
        url=${url##*( )}
        expected=${expected##*( )}

    	# Run curl command to post data to the URL and get the response.
        # [curl_args] is not in quotes so that the values expand into
        # parameters for curl, while the URL variables are includes in
        # quotes so that they combine into a single string.
        response=$(curl ${curl_args} "${root_url}${url}")

    	# Compare response text to the expected result
        if [ "${response}" = "${expected}" ]; then
    		echo "SUCCESS for URL: ${url}"
            echo "Response: \"${response}\""
            ((success_count++))
    	else
            echo "ERROR for URL: ${url}"
            echo "curl_args: \"${curl_args}\""
    		echo "Response: \"${response}\""
            echo "Expected: \"${expected}\""
            has_error=1
    	fi
    done

    # Finished, show the result
    # Return 0 "Success" or 1 "Error" that can be used as the exit code
    echo '============================================================='
    if [ $has_error = 1 ]; then
        echo "ERROR, ${success_count} of ${#tests[@]} Responses returned the Expected Result"
        return 1
    else
        echo "SUCCESS for ${success_count} of ${#tests[@]} Responses"
        return 0
    fi
}


#---------------------------------------------------------
# Run the main() function and exit with the result
#---------------------------------------------------------
main
exit $?
