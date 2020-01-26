/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict:true, esversion:6 */

(function () {
    "use strict"; // Invoke strict mode

    if (window.runTestsRequiringInternet) {
        runHttpUnitTest("Net - SMTP Client - Connect to Gmail SMTP Server", "test-net-smtp.php/connect-to-gmail", {
            response: "[open: smtp.gmail.com 587][EHLO,STARTTLS,EHLO,NOOP,HELP,QUIT][open:220][ehlo:2:250:250][tls:220][noop:250][help:214][quit:221][close]"
        });
    }

    runHttpUnitTest("Net - Email - Create a Basic Email", "test-net-smtp.php/create-basic-email", {
        type: "text",
        response: `Date: <Removed>
Subject: =?utf-8?b?VGhpcyBpcyBhIFRlc3QgRW1haWwgZnJvbSBGYXN0U2l0ZVBIUCB3aXRoIGEgbG9uZyBzdWJqZWN0
 IHRoYXQgd3JhcHMgYW5kIHVzZWQgYmFzZTY0IGVuY29kaW5n?=
To: <name1@example.com>, <name2@example.com>
From: <noreply@example.com>
MIME-Version: 1.0
Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: base64

PGgxPkVtYWlsIFRpdGxlPC9oMT48cCBzdHlsZT0iY29sb3I6Ymx1ZSI+VGhpcyBpcyBhIHRlc3Qu
PC9wPg==
`
    });

    runHttpUnitTest("Net - Email - Create an Advanced Email - Unicode Address, File Attachment, Custom Headers", "test-net-smtp.php/create-advanced-email", {
        type: "text",
        response: `Date: <Removed>
Subject: This is a Test Email with Attached Files
To: <name1@example.com>
Cc: "\\"User\\" Name" <name2@example.com>
From: <无回复@example.com>
Reply-To: "测试" <测试@example.com>
MIME-Version: 1.0
Importance: High
X-Priority: 5
X-Transaction-ID: 123abc
X-Email-Type: Unit-Test
X-Emoji: 😊
X-Encode-Backslash: =?utf-8?b?XA==?=
X-Encode-Quote: =?utf-8?b?Ig==?=
X-Encode-URL: =?utf-8?b?JQ==?=
X-Encode-HTML1: =?utf-8?b?Jg==?=
X-Encode-HTML2: =?utf-8?b?VSs=?=
Strange Header 😊: 👍
Content-Type: multipart/alternative; boundary=--NEXT_PART----

This is a message with multiple parts in MIME format.
----NEXT_PART----
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: base64

VGVzdCBQbGFpbiBUZXh0IEVtYWls
----NEXT_PART----
Content-Type: application/octet-stream;
Content-Disposition: attachment; filename="Test.txt"
Content-Transfer-Encoding: base64

VGhpcyBpcyBhIFRlc3Q=
----NEXT_PART----`
    });

    if (window.runTestsRequiringFileWrite) {
        runHttpUnitTest("Net - Email - Create an Email with Encoded File Names", "test-net-smtp.php/create-email-with-encoded-file-names", {
            type: "text",
            response: `Date: <Removed>
Subject: Test Email with Encoded File Names
To: <name1@example.com>
From: <noreply@example.com>
MIME-Version: 1.0
X-Transaction-ID: 测试-123
Content-Type: multipart/alternative; boundary=--NEXT_PART----

This is a message with multiple parts in MIME format.
----NEXT_PART----
Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: base64

PGgxPkZpbGUgTmFtZSBUZXN0PC9oMT4=
----NEXT_PART----
Content-Type: application/octet-stream;
Content-Disposition: attachment; filename="Test.txt"
Content-Disposition: attachment; filename*=UTF-8''Test.txt
Content-Transfer-Encoding: base64

VGhpcyBpcyBhIFRlc3Q=
----NEXT_PART----
Content-Type: application/octet-stream;
Content-Disposition: attachment; filename="测试.txt"
Content-Disposition: attachment; filename*=UTF-8''%E6%B5%8B%E8%AF%95.txt
Content-Transfer-Encoding: base64

VGhpcyBpcyBhIFRlc3QgdXNpbmcgU2ltcGxpZmllZCBDaGluZXNlDQrmtYvor5UNClRoaXMgaXMg
YSBUZXN0IHVzaW5nIFNpbXBsaWZpZWQgQ2hpbmVzZQ0K5rWL6K+VDQo=
----NEXT_PART----`
        });
    }

    runHttpUnitTest("Net - SMTP and Email - Verify MD5 Hashes of Class Files", "test-net-smtp.php/class-hashes", {
        response: {
            Email: "6ad938065059f769f5e318bfca7bef678723aa1e94e3ffe6fc92b37ad26155ed1c11e11cfd472196232036acc2535cf1",
            SmtpClient: "e02a59f8e3e623b3b37446c69ec7c8434706cc048a1ccc606d75a723fca1104de4eb7cdb459e28f6f25b78bf1d6ae6cf"
        }
    });

})();
