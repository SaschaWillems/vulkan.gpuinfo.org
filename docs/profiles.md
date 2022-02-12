# Vulkan profile support

## About

[Vulkan Profiles](https://github.com/KhronosGroup/Vulkan-Profiles) can be downloaded for all reports available in the database. The profile JSON is generated at runtime and can be used to simulate the device using vkconfig.

## Content

The profile will contain the following device related data:

### Features

All features supported in the selected device report. This includes core feature structs (VkPhysicalDevice*Features) for up to Vulkan 1.3 depending on the device report's api level and extension feature structs that are not part of the max. supported core version for that report. E.g. if VkPhysicalDeviceSomeFeatureFeaturesEXT has become core with 1.1, that feature struct will not be exported for reports with Vulkan 1.1 and newer as it's features are part of the core feature struct. For reports that don't support that core version but the extension, that feature struct will be exported.

### Properties

All properties supported in the selected device report. This includes core feature structs (VkPhysicalDevice*Properties) for up to Vulkan 1.3 depending on the device report's api level and extension feature structs that are not part of the max. supported core version for that report. E.g. if VkPhysicalDeviceSomeFeaturePropertiesEXT has become core with 1.1, that feature struct will not be exported for reports with Vulkan 1.1 and newer as it's properties are part of the core feature struct. For reports that don't support that core version but the extension, that feature struct will be exported.

### Available extensions

A list of all extensions supported by the device **and** supported by the current version of the Vulkan Profile schema. Extensions available in the device report not supported by the current version of the Vulkan Profile schema are not exported.

### Supported formats

All linear and optimal tiling formats as well as buffer formats with their respective usage flags for the selected device report.

### Available queue families

All queue families available for the selected device.

## Portability subset

For devices supporting the `VK_KHR_portability_subset` extension, profiles that only contain portability related features and properties can be created.
