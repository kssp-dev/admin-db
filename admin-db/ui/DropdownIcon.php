<?php

class DropdownIcon extends Atk4\Ui\Form\Control\Dropdown {
	
    #[\Override]
    protected function init(): void
    {
        parent::init();
        
        $list = <<<LIST
			500px
			accessible
			accusoft
			acquisitions incorporated
			ad
			address book
			address book outline
			address card
			address card outline
			adjust
			adn
			adversal
			affiliatetheme
			airbnb
			air freshener
			algolia
			align center
			align justify
			align left
			align right
			alipay
			allergies
			amazon
			amazon pay
			ambulance
			american sign language interpreting
			amilia
			anchor
			android
			angellist
			angle double down
			angle double left
			angle double right
			angle double up
			angle down
			angle left
			angle right
			angle up
			angry
			angrycreative
			angry outline
			angular
			ankh
			apper
			apple
			apple pay
			app store
			app store ios
			archive
			archway
			arrow alternate circle down
			arrow alternate circle down outline
			arrow alternate circle left
			arrow alternate circle left outline
			arrow alternate circle right
			arrow alternate circle right outline
			arrow alternate circle up
			arrow alternate circle up outline
			arrow circle down
			arrow circle left
			arrow circle right
			arrow circle up
			arrow down
			arrow left
			arrow right
			arrows alternate
			arrows alternate horizontal
			arrows alternate vertical
			arrow up
			artstation
			assistive listening systems
			asterisk
			asymmetrik
			at
			atlas
			atlassian
			atom
			audible
			audio description
			autoprefixer
			avianex
			aviato
			award
			aws
			baby
			baby carriage
			backward
			bacon
			bacteria
			bacterium
			bahai
			balance scale
			balance scale left
			balance scale right
			ban
			band aid
			bandcamp
			barcode
			bars
			baseball ball
			basketball ball
			bath
			battery empty
			battery full
			battery half
			battery quarter
			battery three quarters
			battle net
			bed
			beer
			behance
			behance square
			bell
			bell outline
			bell slash
			bell slash outline
			bezier curve
			bible
			bicycle
			biking
			bimobject
			binoculars
			biohazard
			birthday cake
			bitbucket
			bitcoin
			bity
			blackberry
			black tie
			blender
			blind
			blog
			blogger
			blogger b
			bluetooth
			bluetooth b
			bold
			bolt
			bomb
			bone
			bong
			book
			book dead
			bookmark
			bookmark outline
			book medical
			book open
			book reader
			bootstrap
			border all
			border none
			border style
			bowling ball
			box
			boxes
			box open
			box tissue
			braille
			brain
			bread slice
			briefcase
			briefcase medical
			broadcast tower
			broom
			brush
			btc
			buffer
			bug
			building
			building outline
			bullhorn
			bullseye
			burn
			buromobelexperte
			bus
			bus alternate
			business time
			buy n large
			buysellads
			calculator
			calendar
			calendar alternate
			calendar alternate outline
			calendar check
			calendar check outline
			calendar day
			calendar minus
			calendar minus outline
			calendar outline
			calendar plus
			calendar plus outline
			calendar times
			calendar times outline
			calendar week
			camera
			camera retro
			campground
			canadian maple leaf
			candy cane
			cannabis
			capsules
			car
			car alternate
			caravan
			car battery
			car crash
			caret down
			caret left
			caret right
			caret square down
			caret square down outline
			caret square left
			caret square left outline
			caret square right
			caret square right outline
			caret square up
			caret square up outline
			caret up
			carrot
			car side
			cart arrow down
			cart plus
			cash register
			cat
			cc amazon pay
			cc amex
			cc apple pay
			cc diners club
			cc discover
			cc jcb
			cc mastercard
			cc paypal
			cc stripe
			cc visa
			centercode
			centos
			certificate
			chair
			chalkboard
			chalkboard teacher
			charging station
			chart area
			chart bar
			chart bar outline
			chartline
			chart pie
			check
			check circle
			check circle outline
			check double
			check square
			check square outline
			cheese
			chess
			chess bishop
			chess board
			chess king
			chess knight
			chess pawn
			chess queen
			chess rook
			chevron circle down
			chevron circle left
			chevron circle right
			chevron circle up
			chevron down
			chevron left
			chevron right
			chevron up
			child
			chrome
			chromecast
			church
			circle
			circle notch
			circle outline
			city
			clinic medical
			clipboard
			clipboard check
			clipboard list
			clipboard outline
			clock
			clock outline
			clone
			clone outline
			closed captioning
			closed captioning outline
			cloud
			cloud download alternate
			cloudflare
			cloud meatball
			cloud moon
			cloud moon rain
			cloud rain
			cloudscale
			cloud showers heavy
			cloudsmith
			cloud sun
			cloud sun rain
			cloud upload alternate
			cloudversify
			cocktail
			code
			code branch
			codepen
			codiepie
			coffee
			cog
			cogs
			coins
			columns
			comment
			comment alternate
			comment alternate outline
			comment dollar
			comment dots
			comment dots outline
			comment medical
			comment outline
			comments
			comments dollar
			comment slash
			comments outline
			compact disc
			compass
			compass outline
			compress
			compress alternate
			compress arrows alternate
			concierge bell
			confluence
			connectdevelop
			contao
			cookie
			cookie bite
			copy
			copy outline
			copyright
			copyright outline
			cotton bureau
			couch
			cpanel
			creative commons
			creative commons by
			creative commons nc
			creative commons nc eu
			creative commons nc jp
			creative commons nd
			creative commons pd
			creative commons pd alternate
			creative commons remix
			creative commons sa
			creative commons sampling
			creative commons sampling plus
			creative commons share
			creative commons zero
			credit card
			credit card outline
			critical role
			crop
			crop alternate
			cross
			crosshairs
			crow
			crutch
			css3
			css3 alternate
			cube
			cubes
			cut
			cuttlefish
			dailymotion
			d and d
			d and d beyond
			dashcube
			database
			deaf
			deezer
			delicious
			democrat
			deploydog
			deskpro
			desktop
			dev
			deviantart
			dharmachakra
			dhl
			diagnoses
			diaspora
			dice
			dice d20
			dice d6
			dice five
			dice four
			dice one
			dice six
			dice three
			dice two
			digg
			digital ocean
			digital tachograph
			directions
			discord
			discourse
			disease
			divide
			dizzy
			dizzy outline
			dna
			dochub
			docker
			dog
			dollar sign
			dolly
			dolly flatbed
			donate
			door closed
			door open
			dot circle
			dot circle outline
			dove
			download
			draft2digital
			drafting compass
			dragon
			draw polygon
			dribbble
			dribbble square
			dropbox
			drum
			drum steelpan
			drumstick bite
			drupal
			dumbbell
			dumpster
			dungeon
			dyalog
			earlybirds
			ebay
			edge
			edge legacy
			edit
			edit outline
			egg
			eject
			elementor
			ellipsis horizontal
			ellipsis vertical
			ello
			ember
			empire
			envelope
			envelope open
			envelope open outline
			envelope open text
			envelope outline
			envelope square
			envira
			equals
			eraser
			erlang
			ethereum
			ethernet
			etsy
			euro sign
			evernote
			exchange alternate
			exclamation
			exclamation circle
			exclamation triangle
			expand
			expand alternate
			expand arrows alternate
			expeditedssl
			external alternate
			external link square alternate
			eye
			eye dropper
			eye outline
			eye slash
			eye slash outline
			facebook
			facebook f
			facebook messenger
			facebook square
			fan
			fantasy flight games
			fast backward
			fast forward
			faucet
			fax
			feather
			feather alternate
			fedex
			fedora
			female
			fighter jet
			figma
			file
			file alternate
			file alternate outline
			file archive
			file archive outline
			file audio
			file audio outline
			file code
			file code outline
			file contract
			file download
			file excel
			file excel outline
			file export
			file image
			file image outline
			file import
			file invoice
			file invoice dollar
			file medical
			file medical alternate
			file outline
			file pdf
			file pdf outline
			file powerpoint
			file powerpoint outline
			file prescription
			file signature
			file upload
			file video
			file video outline
			file word
			file word outline
			fill
			fill drip
			film
			filter
			fingerprint
			fire
			fire alternate
			fire extinguisher
			firefox
			firefox browser
			first aid
			firstdraft
			first order
			first order alternate
			fish
			fist raised
			flag
			flag checkered
			flag outline
			flag usa
			flask
			flickr
			flipboard
			flushed
			flushed outline
			fly
			folder
			folder minus
			folder open
			folder open outline
			folder outline
			folder plus
			font
			font awesome
			font awesome alternate
			font awesome flag
			fonticons
			fonticons fi
			football ball
			fort awesome
			fort awesome alternate
			forumbee
			forward
			foursquare
			freebsd
			free code camp
			frog
			frown
			frown open
			frown open outline
			frown outline
			fruit-apple
			fulcrum
			funnel dollar
			futbol
			futbol outline
			galactic republic
			galactic senate
			gamepad
			gas pump
			gavel
			gem
			gem outline
			genderless
			get pocket
			gg
			gg circle
			ghost
			gift
			gifts
			git
			git alternate
			github
			github alternate
			github square
			gitkraken
			gitlab
			git square
			gitter
			glass cheers
			glasses
			glass martini
			glass martini alternate
			glass whiskey
			glide
			glide g
			globe
			globe africa
			globe americas
			globe asia
			globe europe
			gofore
			golf ball
			goodreads
			goodreads g
			google
			google drive
			google pay
			google play
			google plus
			google plus g
			google plus square
			google wallet
			gopuram
			graduation cap
			gratipay
			grav
			greater than
			greater than equal
			grimace
			grimace outline
			grin
			grin alternate
			grin alternate outline
			grin beam
			grin beam outline
			grin beam sweat
			grin beam sweat outline
			grin hearts
			grin hearts outline
			grin outline
			grin squint
			grin squint outline
			grin squint tears
			grin squint tears outline
			grin stars
			grin stars outline
			grin tears
			grin tears outline
			grin tongue
			grin tongue outline
			grin tongue squint
			grin tongue squint outline
			grin tongue wink
			grin tongue wink outline
			grin wink
			grin wink outline
			gripfire
			grip horizontal
			grip lines
			grip lines vertical
			grip vertical
			grunt
			guilded
			guitar
			gulp
			hacker news
			hacker news square
			hackerrank
			hamburger
			hammer
			hamsa
			hand holding
			hand holding heart
			hand holding medical
			hand holding usd
			hand holding water
			hand lizard
			hand lizard outline
			hand middle finger
			hand paper
			hand paper outline
			hand peace
			hand peace outline
			hand point down
			hand point down outline
			hand pointer
			hand pointer outline
			hand point left
			hand point left outline
			hand point right
			hand point right outline
			hand point up
			hand point up outline
			hand rock
			hand rock outline
			hands
			hand scissors
			hand scissors outline
			handshake
			handshake alternate slash
			handshake outline
			handshake slash
			hands helping
			hand sparkles
			hand spock
			hand spock outline
			hands wash
			hanukiah
			hard hat
			hashtag
			hat cowboy
			hat cowboy side
			hat wizard
			hdd
			hdd outline
			heading
			headphones
			headphones alternate
			headset
			head side cough
			head side cough slash
			head side mask
			head side virus
			heart
			heartbeat
			heart broken
			heart outline
			helicopter
			highlighter
			hiking
			hippo
			hips
			hire a helper
			history
			hive
			hockey puck
			holly berry
			home
			hooli
			hornbill
			horse
			horse head
			hospital
			hospital alternate
			hospital outline
			hospital symbol
			hospital user
			hotdog
			hotel
			hotjar
			hot tub
			hourglass
			hourglass end
			hourglass half
			hourglass outline
			hourglass start
			house damage
			house user
			houzz
			hryvnia
			h square
			html5
			hubspot
			ice cream
			icicles
			icons
			i cursor
			id badge
			id badge outline
			id card
			id card alternate
			id card outline
			ideal
			igloo
			image
			image outline
			images
			images outline
			imdb
			inbox
			in cart
			indent
			industry
			infinity
			info
			info circle
			innosoft
			instagram
			instagram square
			instalod
			intercom
			internet explorer
			invision
			ioxhost
			italic
			itch io
			itunes
			itunes note
			java
			jedi
			jedi order
			jenkins
			jira
			joget
			joint
			joomla
			journal whills
			js
			jsfiddle
			js square
			kaaba
			kaggle
			key
			keybase
			keyboard
			keyboard outline
			keycdn
			khanda
			kickstarter
			kickstarter k
			kiss
			kiss beam
			kiss beam outline
			kiss outline
			kiss wink heart
			kiss wink heart outline
			kiwi bird
			korvue
			landmark
			language
			laptop
			laptop code
			laptop house
			laptop medical
			laravel
			lastfm
			lastfm square
			laugh
			laugh beam
			laugh beam outline
			laugh outline
			laugh squint
			laugh squint outline
			laugh wink
			laugh wink outline
			layer group
			leaf
			leanpub
			lemon
			lemon outline
			lesscss
			less than
			less than equal
			level down alternate
			level up alternate
			life ring
			life ring outline
			lightbulb
			lightbulb outline
			linechat
			linkedin
			linkedin in
			linkify
			linode
			linux
			lira sign
			list
			list alternate
			list alternate outline
			list ol
			list ul
			location arrow
			lock
			lock open
			log out
			long arrow alternate down
			long arrow alternate left
			long arrow alternate right
			long arrow alternate up
			low vision
			luggage cart
			lungs
			lungs virus
			lyft
			magento
			magic
			magnet
			mail bulk
			mailchimp
			male
			mandalorian
			map
			map marked
			map marked alternate
			map marker
			map marker alternate
			map outline
			map pin
			map signs
			markdown
			marker
			mars
			mars double
			mars stroke
			mars stroke horizontal
			mars stroke vertical
			mask
			mastodon
			maxcdn
			mdb
			medal
			medapps
			medium
			medium m
			medkit
			medrt
			meetup
			megaport
			meh
			meh blank
			meh blank outline
			meh outline
			meh rolling eyes
			meh rolling eyes outline
			memory
			mendeley
			menorah
			mercury
			meteor
			microblog
			microchip
			microphone
			microphone alternate
			microphone alternate slash
			microphone slash
			microscope
			microsoft
			minus
			minus circle
			minus square
			minus square outline
			mitten
			mix
			mixcloud
			mixer
			mizuni
			mobile
			mobile alternate
			modx
			monero
			money bill
			money bill alternate
			money bill alternate outline
			money bill wave
			money bill wave alternate
			money check
			money check alternate
			monument
			moon
			moon outline
			mortar pestle
			mosque
			motorcycle
			mountain
			mouse
			mouse pointer
			mug hot
			music
			Music
			napster
			neos
			neuter
			newspaper
			newspaper outline
			nimblr
			node
			node js
			not equal
			notes medical
			npm
			ns8
			nutritionix
			object group
			object group outline
			object ungroup
			object ungroup outline
			octopus deploy
			odnoklassniki
			odnoklassniki square
			oil can
			old republic
			om
			opencart
			openid
			opera
			optin monster
			orcid
			osi
			otter
			outdent
			page4
			pagelines
			pager
			paint brush
			paint roller
			palette
			palfed
			pallet
			paperclip
			paper plane
			paper plane outline
			parachute box
			paragraph
			parking
			passport
			pastafarianism
			paste
			patreon
			pause
			pause circle
			pause circle outline
			paw
			paypal
			peace
			pen
			pen alternate
			pencil alternate
			pencil ruler
			pen fancy
			pen nib
			penny arcade
			pen square
			people arrows
			people carry
			pepper hot
			perbyte
			percent
			percentage
			periscope
			person booth
			phabricator
			phoenix framework
			phoenix squadron
			phone
			phone alternate
			phone slash
			phone square
			phone square alternate
			phone volume
			photo video
			php
			pied piper
			pied piper alternate
			pied piper hat
			pied piper pp
			pied piper square
			piggy bank
			pills
			pinterest
			pinterest p
			pinterest square
			pizza slice
			place of worship
			plane
			plane arrival
			plane departure
			play
			play circle
			play circle outline
			playstation
			plug
			plus
			plus circle
			plus square
			plus square outline
			podcast
			poll
			poll horizontal
			poo
			poop
			poo storm
			portrait
			pound sign
			power off
			pray
			praying hands
			prescription
			prescription bottle
			prescription bottle alternate
			print
			procedures
			product hunt
			project diagram
			pump medical
			pump soap
			pushed
			puzzle piece
			python
			qq
			qrcode
			question
			question circle
			question circle outline
			quidditch
			quinscape
			quora
			quote left
			quote right
			quran
			radiation
			radiation alternate
			rainbow
			random
			raspberry pi
			ravelry
			react
			reacteurope
			readme
			rebel
			receipt
			record vinyl
			recycle
			reddit
			reddit alien
			reddit square
			redhat
			redo
			redo alternate
			redriver
			redyeti
			registered
			registered outline
			remove format
			renren
			reply
			reply all
			replyd
			republican
			researchgate
			resolving
			restroom
			retweet
			rev
			ribbon
			ring
			road
			robot
			rocket
			rocketchat
			rockrms
			route
			r project
			rss
			rss square
			ruble sign
			ruler
			ruler combined
			ruler horizontal
			ruler vertical
			running
			rupee sign
			rust
			sad cry
			sad cry outline
			sad tear
			sad tear outline
			safari
			salesforce
			sass
			satellite
			satellite dish
			save
			save outline
			schlix
			school
			screwdriver
			scribd
			scroll
			sd card
			search
			search dollar
			searchengin
			search location
			search minus
			search plus
			seedling
			sellcast
			sellsy
			server
			servicestack
			shapes
			share
			share alternate
			share alternate square
			share square
			share square outline
			shekel sign
			shield alternate
			shield virus
			ship
			shipping fast
			shirtsinbulk
			shoe prints
			shopify
			shopping bag
			shopping basket
			shopping cart
			shopware
			shower
			shuttle van
			sign
			signal
			sign in
			sign in alternate
			sign language
			sign out
			sign out alternate
			sim card
			simplybuilt
			sink
			sistrix
			sitemap
			sith
			skating
			sketch
			skiing
			skiing nordic
			skull crossbones
			skyatlas
			skype
			slack
			slack hash
			slash
			sleigh
			sliders horizontal
			slideshare
			smile
			smile beam
			smile beam outline
			smile outline
			smile wink
			smile wink outline
			smog
			smoking
			smoking ban
			sms
			snapchat
			snapchat ghost
			snapchat square
			snowboarding
			snowflake
			snowflake outline
			snowman
			snowplow
			soap
			socks
			solar panel
			sort
			sort alphabet down
			sort alphabet down alternate
			sort alphabet up
			sort alphabet up alternate
			sort amount down
			sort amount down alternate
			sort amount up
			sort amount up alternate
			sort down
			sort numeric down
			sort numeric down alternate
			sort numeric up
			sort numeric up alternate
			sort up
			soundcloud
			sourcetree
			spa
			space shuttle
			speakap
			speaker deck
			spell check
			spider
			spinner
			splotch
			spotify
			spray can
			square
			square full
			square outline
			square root alternate
			squarespace
			stack exchange
			stack overflow
			stackpath
			stamp
			star
			star and crescent
			star half
			star half alternate
			star half outline
			star of david
			star of life
			star outline
			staylinked
			steam
			steam square
			steam symbol
			step backward
			step forward
			stethoscope
			sticker mule
			sticky note
			sticky note outline
			stop
			stop circle
			stop circle outline
			stopwatch
			store
			store alternate
			store alternate slash
			store slash
			strava
			stream
			street view
			strikethrough
			stripe
			stripe s
			stroopwafel
			studiovinari
			stumbleupon
			stumbleupon circle
			subscript
			subway
			suitcase
			suitcase rolling
			sun
			sun outline
			superpowers
			superscript
			supple
			surprise
			surprise outline
			suse
			swatchbook
			swift
			swimmer
			swimming pool
			symfony
			synagogue
			sync
			sync alternate
			syringe
			table
			tablet
			tablet alternate
			table tennis
			tablets
			tachometer alternate
			tag
			tags
			tape
			tasks
			taxi
			teamspeak
			teeth
			teeth open
			telegram
			telegram plane
			temperature high
			temperature low
			tencent weibo
			tenge
			terminal
			text height
			text width
			th
			theater masks
			themeco
			themeisle
			thermometer
			thermometer empty
			thermometer full
			thermometer half
			thermometer quarter
			thermometer three quarters
			think peaks
			th large
			th list
			thumbs down
			thumbs down outline
			thumbs up
			thumbs up outline
			thumbtack
			ticket alternate
			tiktok
			times
			times circle
			times circle outline
			tint
			tint slash
			tired
			tired outline
			toggle off
			toggle on
			toilet
			toilet paper
			toilet paper slash
			toolbox
			tools
			tooth
			torah
			torii gate
			tractor
			trade federation
			trademark
			traffic light
			trailer
			train
			tram
			transgender
			transgender alternate
			trash
			trash alternate
			trash alternate outline
			trash restore
			trash restore alternate
			tree
			trello
			trophy
			truck
			truck monster
			truck moving
			truck packing
			truck pickup
			tshirt
			tty
			tumblr
			tumblr square
			tv
			twitch
			twitter
			twitter square
			typo3
			uber
			ubuntu
			uikit
			umbraco
			umbrella
			umbrella beach
			uncharted
			underline
			undo
			undo alternate
			uniregistry
			unity
			universal access
			university
			unlink
			unlock
			unlock alternate
			unsplash
			untappd
			upload
			ups
			usb
			user
			user alternate
			user alternate slash
			user astronaut
			user check
			user circle
			user circle outline
			user clock
			user cog
			user edit
			user friends
			user graduate
			user injured
			user lock
			user md
			user minus
			user ninja
			user nurse
			user outline
			user plus
			users
			users cog
			user secret
			user shield
			user slash
			users slash
			user tag
			user tie
			user times
			usps
			ussunnah
			utensils
			utensil spoon
			vaadin
			vector square
			venus
			venus double
			venus mars
			vest
			vest patches
			viacoin
			viadeo
			viadeo square
			vial
			vials
			viber
			video
			video slash
			vihara
			vimeo
			vimeo square
			vimeo v
			vine
			virus
			viruses
			virus slash
			vk
			vnv
			voicemail
			volleyball ball
			volume down
			volume mute
			volume off
			volume up
			vote yea
			vuejs
			walking
			wallet
			warehouse
			watchman monitoring
			water
			wave square
			waze
			weebly
			weibo
			weight
			weixin
			whatsapp
			whatsapp square
			wheelchair
			whmcs
			wifi
			wikipedia w
			wind
			window close
			window close outline
			window maximize
			window maximize outline
			window minimize
			window minimize outline
			window restore
			window restore outline
			windows
			wine bottle
			wine glass
			wine glass alternate
			wix
			wizards of the coast
			wodu
			wolf pack battalion
			won sign
			wordpress
			wordpress simple
			wpbeginner
			wpexplorer
			wpforms
			wpressr
			wrench
			xbox
			xing
			xing square
			x ray
			yahoo
			yammer
			yandex
			yandex international
			yarn
			y combinator
			yelp
			yen sign
			yin yang
			yoast
			youtube
			youtube square
			zhihu
			zoom in
			zoom out
		LIST;
		
		$this->values = [];
		
		foreach (explode("\n", $list) as $str) {
			$str = trim($str);

			if (!empty($str)) {
				$this->values[$str] = [$str, 'icon' => $str];
			}
		}
    }	

}

?>
