# Database maintenance

**Note:** This document describes regular maintenance tasks and is not aimed at users of the database

## New profile release

If new profiles are released at https://github.com/KhronosGroup/Khronos-Schemas:
- run tools/generate_mappings.php to update the mapping.json file
- run tools/update_profiles.php to fetch latest profile schemas
- If the profile version has changed:
  - Update profile file names in api/v3/getprofile.php in the `loadSchema` function