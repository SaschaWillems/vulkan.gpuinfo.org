# Vulkan Hardware Database API overview

<!-- vscode-markdown-toc -->
* [Introduction](#Introduction)
* [Note](#Note)
* [Endpoints](#Endpoints)
	* [Reports](#Reports)
		* [Vulkan Profile](#VulkanProfile)
		* [Report](#Report)
	* [Driver coverage](#Drivercoverage)
		* [Extension driver coverage](#Extensiondrivercoverage)
		* [Feature driver coverage](#Featuredrivercoverage)
		* [Extension feature driver coverage](#Extensionfeaturedrivercoverage)
		* [Surface present mode driver coverage](#Surfacepresentmodedrivercoverage)
		* [Subgroup stage driver coverage](#Subgroupstagedrivercoverage)
		* [Subgroup operation driver coverage](#Subgroupoperationdrivercoverage)

<!-- vscode-markdown-toc-config
	numbering=false
	autoSave=true
	/vscode-markdown-toc-config -->
<!-- /vscode-markdown-toc -->

# <a name='Introduction'></a>Introduction
The Vulkan Hardware Database offers several API endpoints for fetching data based on different input parameters. Data is returned as JSON.

# <a name='Note'></a>Note
Please note that the API is **not designed to allow full database access** but rather offers different data sets based on requests from different IHVs and ISVs.

Also note that if you plan on using the API, **don't use it for creating your own applications** and **don't excessively request data** without asking me for consent. 

All hosting is paid by myself, so please ask before using the API in such a way.

# <a name='Endpoints'></a>Endpoints

## <a name='Reports'></a>Reports

Report endpoints return data for a single report.

### <a name='VulkanProfile'></a>Vulkan Profile

The profile endpoint returns a JSON for a Vulkan profile to be used with [LunarG's Vulkan Profiles Layer](https://github.com/KhronosGroup/Vulkan-Profiles). Details on how profiles are implemented in the database can be found in [this document](./profiles.md).

| Type | Value | Remark |
| - | - | - |
| URL | `https://vulkan.gpuinfo.org/api/v3/getprofile` | `GET` method |
| URL Parameter | `id` **required** | The ID of the report for which to download the profile. |

Example: https://vulkan.gpuinfo.org/api/v3/getprofile?id=14204

### <a name='Report'></a>Report

This endpoint returns a custom JSON for a single report. Unlike the profile JSON this JSON contains all information for a single report stored in the database.

| Type | Value | Remark |
| - | - | - |
| URL | `https://vulkan.gpuinfo.org/api/v2/getreport` | `GET` method |
| URL Parameter | `id` **required** | The ID of the report for which to download the report. |

Example: https://vulkan.gpuinfo.org/api/v2/getreport?id=14204

## <a name='Drivercoverage'></a>Driver coverage

Driver coverage endpoints return a list of devices supporting the requested property along with the first known driver version to support it.

Common result structure:

```json
{
  "query": {
      # Details of the query data
  },
  "devices": [
    {
        # device 0
    },
    ...
    {
        # device n
    },
```

Example:

```json
{
  "query": {
    "feature": "sparseResidency16Samples",
    "platform": "android",
    "resultcount": 2
  },
  "devices": [
    {
      "device": "Google Pixel C",
      "api": "4194317",
      "driverversion": "361.0.0.0",
      "driverversionraw": "1514143744",
      "deviceid": "0x92BA03D7",
      "vendorid": "0x10DE",
      "vendor": "NVIDIA",
      "submissiondate": "2017-06-13",
      "platform": "android"
    },
    {
      "device": "NVIDIA SHIELD TV",
      "api": "4194360",
      "driverversion": "361.0.0.0",
      "driverversionraw": "1514143744",
      "deviceid": "0x92BA03D7",
      "vendorid": "0x10DE",
      "vendor": "NVIDIA",
      "submissiondate": "2017-10-21",
      "platform": "android"
    }
  ]
}
```

### <a name='Extensiondrivercoverage'></a>Extension driver coverage

Returns a JSON array of devices and known driver version supporting the requested device extension.

| Type | Value | Remark |
| - | - | - |
| URL | `https://vulkan.gpuinfo.org/api/v2/drivercoverage/extension` | `GET` method |
| URL Parameter | `extension` **required** | The device extension to list devices for. Must be a known extension present on the database. |
| URL Parameter | `platform` **optional** | Platform to limit results to (Windows, Linux, Android), if not supplied, devices for all platforms are returned. |

Example: https://vulkan.gpuinfo.org/api/v2/drivercoverage/extension?extension=VK_KHR_create_renderpass2&platform=windows

### <a name='Featuredrivercoverage'></a>Feature driver coverage

Returns a JSON array of devices and known driver version supporting the requested device feature.

| Type | Value | Remark |
| - | - | - |
| URL | `https://vulkan.gpuinfo.org/api/v2/drivercoverage/feature` | `GET` method |
| URL Parameter | `feature` **required** | The device feature to list devices for. Must be a known feature present on the database. |
| URL Parameter | `platform` **optional** | Platform to limit results to (Windows, Linux, Android), if not supplied, devices for all platforms are returned. |

Example: https://vulkan.gpuinfo.org/api/v2/drivercoverage/feature?feature=sparseResidency16Samples&platform=windows

### <a name='Extensionfeaturedrivercoverage'></a>Extension feature driver coverage

Returns a JSON array of devices and known driver version supporting the requested device extension feature (`VkPhysicalDeviceFeatures2`).

| Type | Value | Remark |
| - | - | - |
| URL | `https://vulkan.gpuinfo.org/api/v2/drivercoverage/feature2`` | `GET` method |
| URL Parameter | `extension` **required** | The device extension to list devices for. Must be a known extension present on the database. |
| URL Parameter | `feature` **required** | The device extension feature to request support for. Must be a known feature for the requested extension. |
| URL Parameter | `platform` **optional** | Platform to limit results to (Windows, Linux, Android), if not supplied, devices for all platforms are returned. |

Example: https://vulkan.gpuinfo.org/api/v2/drivercoverage/feature2?extension=VK_KHR_16bit_storage&feature=storageInputOutput16&platform=windows

### <a name='Surfacepresentmodedrivercoverage'></a>Surface present mode driver coverage

Returns a JSON array of devices and known driver version supporting the surface present mode.

| Type | Value | Remark |
| - | - | - |
| URL | `https://vulkan.gpuinfo.org/api/v2/drivercoverage/presentmode` | `GET` method |
| URL Parameter | `presentmode` **required** | The surface present mode to list devices for. Must be a known ```VkPresentModeKHR``` value. |
| URL Parameter | `platform` **optional** | Platform to limit results to (Windows, Linux, Android), if not supplied, devices for all platforms are returned. |

Example: https://vulkan.gpuinfo.org/api/v2/drivercoverage/surface/presentmode?presentmode=VK_PRESENT_MODE_SHARED_DEMAND_REFRESH_KHR&platform=android

### <a name='Subgroupstagedrivercoverage'></a>Subgroup stage driver coverage

Returns a JSON array of devices and known driver version supporting the subgroup stage.

| Type | Value | Remark |
| - | - | - |
| URL | `https://vulkan.gpuinfo.org/api/v2/drivercoverage/subgroups/stage` | `GET` method |
| URL Parameter | `stage` **required** | The subgroup stage to list devices for. Must be a known ```VkShaderStageFlagBits``` value valid for subgroup stages.   |
| URL Parameter | `platform` **optional** | Platform to limit results to (Windows, Linux, Android), if not supplied, devices for all platforms are returned. |

Example: https://vulkan.gpuinfo.org/api/v2/drivercoverage/subgroups/stage?stage=VK_SHADER_STAGE_TESSELLATION_CONTROL_BIT&platform=windows

### <a name='Subgroupoperationdrivercoverage'></a>Subgroup operation driver coverage

Returns a JSON array of devices and known driver version supporting the subgroup operation.

| Type | Value | Remark |
| - | - | - |
| URL | `https://vulkan.gpuinfo.org/api/v2/drivercoverage/subgroups/operation` | `GET` method |
| URL Parameter | `operation` **required** | The surface present mode to list devices for. Must be a known `VkSubgroupFeatureFlagBits` value. |
| URL Parameter | `platform` **optional** | Platform to limit results to (Windows, Linux, Android), if not supplied, devices for all platforms are returned. |

Example: https://vulkan.gpuinfo.org/api/v2/drivercoverage/subgroups/operation?operation=VK_SUBGROUP_FEATURE_SHUFFLE_RELATIVE_BIT&platform=linux