## niedlich.org
Website and API to retrieve cute files of cute animals

### niedlich [needlikh]
adjective, German: cute

From the same stem as the English world "Needly" it describes something that is so cute, you just NEED it.

### License

This project is licensed under a custom Non-Commercial Open Source License.
See [LICENSE.md](LICENSE.md) for the full text.

#### Important Notes for Contributors

By submitting code to this repository, you agree to the Contributor License Agreement in Section 4 of the license, including:
- You are the original author of all submitted code
- You are not submitting AI-generated code
- You grant the project maintainer broad rights to use your contribution, to maintain the integrity of the project

See [CONTRIBUTERS.md](CONTRIBUTERS.md) for contribution credits.

### Features / TODO

- [ ] Database System to store media of cute animals directly
  - [ ] Integrate Options for CDN/S3 Storage
- [ ] Category(Taxonomy) & Tagging System
  - [ ] Metadata: Species, Description, Author, License, Source, etc.
- [ ] API Endpoint to receive random cute media according to requirements
  - [ ] API Versioning
  - [ ] API Documentation
  - [ ] Return cute cat images for errors
- [ ] Config for Ratelimits, Bandwidth limits, trust levels, rangeblocking, etc to prevent abuse
- [ ] User Accounts to submit and manage cute media files
  - [ ] Add Algorithm to suggest category and tags based on current media with the same tags/categories
  - [ ] Detect inappropriate content and duplicates
  - [ ] API Authentication for higher rate limits and saved searches
  - [ ] Public profile with stats, favorites, etc.
  - [ ] Badges!
- [ ] Proper Moderation tools
  - [ ] Moderation Queue
  - [ ] Control Panel
- [ ] Website: Form to easily report media (Wrong Tags/Categories, Inappropriate, Copyright)
  - [ ] Add categories for general Feedback (Improvements, Bugs, ...)
- [ ] Website: Possibility to browse/search images
- [ ] Match Game where a User is presented with 2 pictures and has to decide which one is cuter -> Build a cuteness-ranking
- [ ] Webhook system for the creation of Bots and the like
- [ ] Extended GIF Support
  - [ ] Better recognition of doublettes
  - [ ] Better automatic tag assignment
- [ ] Accessibility
  - [ ] Website: Option to load/download full-res image
  - [ ] Website: Mobile Optimization
  - [ ] Dark Theme
  - [ ] Verify contrasts
- [ ] Cute Facts
  - [ ] Endpoint to get cute facts
  - [ ] Same Categories(Animal Taxonomy) but own tags seperate from media
  - [ ] Endpoint that overlays the fact onto a random image/gif?
  - [ ] Submit facts
- [ ] Bots (Separate Projects)
  - [ ] Discord
  - [ ] Telegram
  - [ ] X?
