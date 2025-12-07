---
slug: torrentpier-3-announcement
title: "Let's talk about TorrentPier 3.0"
authors: [exileum]
tags: [announcement, development, v3]
---

For several years, we've been developing TorrentPier in a fairly conservative manner — we have version 2, and within it we release more or less minor versions, which sometimes (for no particular reason) we give a new code name and bump the minor number instead of the patch. Since this forum appeared, we've never considered version 3.0 as something that wouldn't necessarily be written from scratch on Laravel or any other framework (Zend, Yii, Symfony). There have been so many attempts to write this mythical version that it's actually hard to count. As you can see, all unsuccessful.

<!-- truncate -->

Over time, these attempts began to tire everyone out, and the engine development has been carried out by a couple of people in a sluggish manner for years now. A couple of years ago, belomaxorka joined the project, who turned out to be a bit more interested in it and put enormous effort into developing the engine, but we still remained somewhere beyond time in terms of the codebase state. Some time ago, I reconnected to the project development, managed to do several interesting things, but again the question arose — why is the engine still called 2, even though it's already 2.8, but most importantly — why don't we follow semantic versioning. After all, the changes we've made over the past couple of months definitely don't fall under the definition of a minor version anymore.

## The core problem

Here we face a simple problem that we've had for a very long time and which hasn't gone anywhere. Yes, we can beautifully rewrite some part of the engine, but it's difficult for users to update. The engine has historically been in such a state that it's hard to even make changes to templates, let alone support mods.

It turned out that it's possible to do this now, without completely rewriting the engine from scratch. How? We'll tell you about this soon in a series of articles about upcoming changes in the new version. The very appearance of an article here means that what's described in the topic already exists and is available in the master branch in the repository, so enthusiasts can already go and try the new feature.

## The key change: mod support

The key change that will appear in version 3.0 I can name right away — **mod support**. It seems this is what stops almost everyone from updating to new versions. This is the most difficult change that will require and has already required major changes in the project, but you'll learn about all this in the series of articles about upcoming changes.

## Timeline and what's next

Obviously, implementing the current remaining backlog by the end of the year definitely won't work out, but in early 2026 the new version will definitely be released. After its release, we will completely switch to semantic versioning and publicly publish the project development roadmap.

Perhaps the current state of the project doesn't allow us to fully compete with other forum engines, but honestly — we're not even trying anymore. We will have big and good new features and changes, but the key thing we're making the project for — doesn't change.

**We make TorrentPier, an engine that gives everyone the ability to run their own torrent tracker. And so it will remain.**
