-- This is a single SQL Statement that returns recent web traffic from the page [examples/database-demo].
-- The [User-Agent] string is parsed using SQL to 4 fields [Device Type, OS Type, OS, and Browser].
-- The CASE statements used works with SQLite and using slight modifications MS SQL Server (see comments
-- in the statement). This is not indented as a full feature [User-Agent] parser but rather it is
-- designed around OS's and Browsers that are in active use. As of 2017 this excludes formerly 
-- common browsers such as IE 6 0 - IE 9. Since the formula is in SQL it allows for a developer 
-- to quickly make changes as needed and use in environment where changing the db or adding 
-- functions, fields, etc is not possible. In each [CASE] statement the [WHEN] rules are defined
-- in specific order so they show correctly.
SELECT
	id,
    url,
    method,
    user_agent,
    date_requested,
	-- Choose CSS Class based on the URL
	CASE
		WHEN url LIKE '%/red' THEN 'red'
		WHEN url LIKE '%/green' THEN 'green'
		WHEN url LIKE '%/blue' THEN 'blue'
		WHEN url LIKE '%/yellow' THEN 'yellow'
		WHEN url LIKE '%/database-demo' THEN 'default'
		ELSE 'black'
	END as class_name,
	CASE
		-- Google and Bing bots will typically show specific devieces, browsers, etc
		-- however this code simply shows them as a Bot.
		WHEN user_agent LIKE '%bingbot/%' THEN 'Bot'
		WHEN user_agent LIKE '%Googlebot/%' THEN 'Bot'
		-- Phone or Tablet can be determined from the User-Agent on Android:
		-- https://developer.chrome.com/multidevice/user-agent
		-- https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent/Firefox
		WHEN user_agent LIKE '%Android% Chrome/[0-9]% Mobile%'
			OR user_agent LIKE '%Android% AppleWebKit/[0-9]% Mobile%'
			OR user_agent LIKE '%Android% Mobile;% Firefox/%' THEN 'Mobile / Phone'
		WHEN user_agent LIKE '%Android% Chrome/[0-9]% Safari%'
			OR user_agent LIKE '%Android% AppleWebKit/[0-9]% Safari%'
			OR user_agent LIKE '%Android% Tablet;% Firefox/%' THEN 'Mobile / Tablet'
		WHEN user_agent LIKE '%Android%' THEN 'Mobile'
		WHEN user_agent LIKE '%Windows NT%' THEN 'Desktop'
		WHEN user_agent LIKE '%iPhone%' THEN 'Mobile / Phone'
		WHEN user_agent LIKE '%iPad%' THEN 'Mobile / Tablet'
		WHEN user_agent LIKE '%Macintosh; Intel Mac OS X%' THEN 'Desktop'
		WHEN user_agent LIKE '%Intel Mac OS X%' THEN 'Desktop'
		WHEN user_agent LIKE '%X11; Linux%' THEN 'Desktop'
		WHEN user_agent LIKE '%X11; Ubuntu;%' THEN 'Desktop'
		WHEN user_agent LIKE '%X11; CrOS%' THEN 'Desktop'
		WHEN user_agent LIKE '%Windows Phone%' THEN 'Mobile / Phone'
		-- For Puffin See this:
		-- https://www.puffinbrowser.com/help/faq/?device=Android
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%'
			AND (user_agent LIKE '%AP' OR user_agent LIKE '%IP') THEN 'Mobile / Phone'
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%'
			AND (user_agent LIKE '%AT' OR user_agent LIKE '%IT') THEN 'Mobile / Tablet'
		WHEN user_agent LIKE '%BB10;%' THEN 'Mobile / Phone'
		WHEN user_agent LIKE '%RIM Tablet%' THEN 'Mobile / Tablet'
		WHEN user_agent LIKE '%Linux x86_64%' THEN 'Desktop'
		WHEN user_agent LIKE '%bot%' THEN 'Bot'
	END AS device_type,
	CASE	
		WHEN user_agent LIKE '%BB10;%' THEN 'Blackberry'
		WHEN user_agent LIKE '%Android%' THEN 'Android'
		WHEN user_agent LIKE '%Windows NT%' THEN 'Windows'
		WHEN user_agent LIKE '%iPhone%' THEN 'iOS'
		WHEN user_agent LIKE '%iPad%' THEN 'iOS'
		WHEN user_agent LIKE '%Macintosh; Intel Mac OS X%' THEN 'Mac'
		WHEN user_agent LIKE '%Intel Mac OS X%' THEN 'Mac'
		WHEN user_agent LIKE '%X11; Linux%' THEN 'Linux'
		WHEN user_agent LIKE '%X11; Ubuntu;%' THEN 'Linux'
		WHEN user_agent LIKE '%X11; CrOS%' THEN 'Chrome OS'
		WHEN user_agent LIKE '%Windows Phone%' THEN 'Windows Phone'
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%' AND user_agent LIKE '%AT' THEN 'Android'
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%' AND user_agent LIKE '%AP' THEN 'Android'
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%' AND user_agent LIKE '%IT' THEN 'iOS'
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%' AND user_agent LIKE '%IP' THEN 'iOS'
		WHEN user_agent LIKE '%RIM Tablet%' THEN 'Blackberry'
		WHEN user_agent LIKE '%Linux x86_64%' THEN 'Linux'
		WHEN user_agent LIKE '%bingbot/%' THEN 'Bot'
		WHEN user_agent LIKE '%Googlebot/%' THEN 'Bot'		
		WHEN user_agent LIKE '%bot%' THEN 'Bot'
	END AS os_type,
	CASE
		WHEN user_agent LIKE '%Android %;%' THEN 
            -- SQLite Syntax
            SUBSTR(
              	user_agent,
              	INSTR(user_agent, 'Android')-1,
                INSTR(SUBSTR(user_agent, INSTR(user_agent, 'Android')), ';')
            )
            -- MS SQL Server Syntax:
            -- SUBSTRING(
            --     user_agent, 
            --     CHARINDEX('Android', user_agent), 
            --     CHARINDEX(';', user_agent, CHARINDEX('Android', user_agent)) - CHARINDEX('Android', user_agent)
            -- )        
		WHEN user_agent LIKE '%Windows NT 10.0%' THEN 'Windows 10'
		WHEN user_agent LIKE '%Windows NT 6.3%' THEN 'Windows 8.1'
		WHEN user_agent LIKE '%Windows NT 6.2%' THEN 'Windows 8'
		WHEN user_agent LIKE '%Windows NT 6.1%' OR user_agent LIKE '%Windows NT6.1%' THEN 'Windows 7'
		WHEN user_agent LIKE '%Windows NT 6.0%' THEN 'Windows Vista'
		WHEN user_agent LIKE '%Windows NT 5.2%' THEN 'Windows XP x64 Edition'
		WHEN user_agent LIKE '%Windows NT 5.1%' THEN 'Windows XP'
		WHEN user_agent LIKE '%Windows NT 5.0%' THEN 'Windows 2000'
		WHEN user_agent LIKE '%iPhone%' THEN 'iOS / iPhone'
		WHEN user_agent LIKE '%iPad%' THEN 'iOS / iPad'
		WHEN user_agent LIKE '%Macintosh; Intel Mac OS X%' THEN 'Mac OS X'
		WHEN user_agent LIKE '%Intel Mac OS X%' THEN 'Mac OS X'
		WHEN user_agent LIKE '%X11; Linux%' THEN 'Linux'
		WHEN user_agent LIKE '%X11; Ubuntu;%' THEN 'Linux'
		WHEN user_agent LIKE '%X11; CrOS%' THEN 'Chrome OS'
		WHEN user_agent LIKE '%Windows Phone%' THEN 'Windows Phone'
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%' AND user_agent LIKE '%AT' THEN 'Android Tablet (Proxy Server)'
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%' AND user_agent LIKE '%AP' THEN 'Android Phone (Proxy Server)'
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%' AND user_agent LIKE '%IT' THEN 'iOS / iPad (Proxy Server)'
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%' AND user_agent LIKE '%IP' THEN 'iOS / iPhone (Proxy Server)'
		WHEN user_agent LIKE '%BB10;%' THEN 'Blackberry'
		WHEN user_agent LIKE '%RIM Tablet%' THEN 'Blackberry PlayBook'
		WHEN user_agent LIKE '%Linux x86_64%' THEN 'Linux'
		WHEN user_agent LIKE '%bingbot/%' THEN 'Bing Bot'
		WHEN user_agent LIKE '%Googlebot/%' THEN 'Google Bot'		
	END AS os,
	CASE
		WHEN user_agent LIKE '%X11; U; Linux%' AND user_agent LIKE '%Puffin%' THEN 'Puffin'
		WHEN user_agent LIKE '%Edg/%' AND user_agent LIKE '%Chrome/%' THEN 'Edge (Chromium)'
		WHEN user_agent LIKE '%Edge/%' AND user_agent LIKE '%Chrome/%' THEN 'Edge (EdgeHTML)'
		WHEN user_agent LIKE '%UBrowser/%' AND user_agent LIKE '%Chrome/%' THEN 'UC Browser'
		WHEN user_agent LIKE '%OPR/%' AND user_agent LIKE '%Chrome/%' THEN 'Opera'
		WHEN user_agent LIKE '%Silk/%' AND user_agent LIKE '%AppleWebKit/%' THEN 'Silk (Kindle)'
		WHEN user_agent LIKE '%AOLBuild/%' AND user_agent LIKE '%Chrome/%' THEN 'AOL / Chrome'
		WHEN user_agent LIKE '%AOLBuild/%' AND user_agent LIKE '%Trident/7.0%' THEN 'AOL / IE 11'
		WHEN user_agent LIKE '%AOLBuild/%' THEN 'AOL / Other'
		WHEN user_agent LIKE '%Chrome/%' THEN 'Chrome'
		WHEN user_agent LIKE '%Mac OS X%' AND user_agent LIKE '%Safari/%' THEN 'Safari'
		WHEN user_agent LIKE '%Mac OS X%' AND user_agent LIKE '%AppleWebKit/%' THEN 'Safari'
		-- https://en.wikipedia.org/wiki/Trident_(layout_engine)
		WHEN user_agent LIKE '%Windows%' AND user_agent LIKE '%Trident/7.0%' THEN 'IE 11'
		WHEN user_agent LIKE '%Windows%' AND user_agent LIKE '%MSIE 10.0;%' AND user_agent LIKE '%Trident/6.0%' THEN 'IE 10'
		WHEN user_agent LIKE '%Windows%' AND user_agent LIKE '%MSIE 7.0;%' AND user_agent LIKE '%Trident/6.0%' THEN 'IE 10 (Compat IE 7)'
		WHEN user_agent LIKE '%Firefox/%' THEN 'Firefox'
		WHEN user_agent LIKE '%AppleWebKit/%' AND user_agent LIKE '%Safari/%' THEN 'Webkit'
		WHEN user_agent LIKE '%bingbot/%' THEN 'Bot'
		WHEN user_agent LIKE '%Googlebot/%' THEN 'Bot'		
	END AS browser
FROM requests
ORDER BY
    id DESC
LIMIT 20
