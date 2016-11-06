CREATE TABLE `deviceextensions` (
  `reportid` int(11) NOT NULL,
  `extensionid` int(11) NOT NULL,
  `specversion` int(11) DEFAULT NULL,
  PRIMARY KEY (`reportid`,`extensionid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `devicefeatures` (
  `reportid` int(11) NOT NULL,
  `alphaToOne` int(11) DEFAULT NULL,
  `depthBiasClamp` int(11) DEFAULT NULL,
  `depthBounds` int(11) DEFAULT NULL,
  `depthClamp` int(11) DEFAULT NULL,
  `drawIndirectFirstInstance` int(11) DEFAULT NULL,
  `dualSrcBlend` int(11) DEFAULT NULL,
  `fillModeNonSolid` int(11) DEFAULT NULL,
  `fragmentStoresAndAtomics` int(11) DEFAULT NULL,
  `fullDrawIndexUint32` int(11) DEFAULT NULL,
  `geometryShader` int(11) DEFAULT NULL,
  `imageCubeArray` int(11) DEFAULT NULL,
  `independentBlend` int(11) DEFAULT NULL,
  `inheritedQueries` int(11) DEFAULT NULL,
  `largePoints` int(11) DEFAULT NULL,
  `logicOp` int(11) DEFAULT NULL,
  `multiDrawIndirect` int(11) DEFAULT NULL,
  `multiViewport` int(11) DEFAULT NULL,
  `occlusionQueryPrecise` int(11) DEFAULT NULL,
  `pipelineStatisticsQuery` int(11) DEFAULT NULL,
  `robustBufferAccess` int(11) DEFAULT NULL,
  `sampleRateShading` int(11) DEFAULT NULL,
  `samplerAnisotropy` int(11) DEFAULT NULL,
  `shaderClipDistance` int(11) DEFAULT NULL,
  `shaderCullDistance` int(11) DEFAULT NULL,
  `shaderFloat64` int(11) DEFAULT NULL,
  `shaderImageGatherExtended` int(11) DEFAULT NULL,
  `shaderInt16` int(11) DEFAULT NULL,
  `shaderInt64` int(11) DEFAULT NULL,
  `shaderResourceMinLod` int(11) DEFAULT NULL,
  `shaderResourceResidency` int(11) DEFAULT NULL,
  `shaderSampledImageArrayDynamicIndexing` int(11) DEFAULT NULL,
  `shaderStorageBufferArrayDynamicIndexing` int(11) DEFAULT NULL,
  `shaderStorageImageArrayDynamicIndexing` int(11) DEFAULT NULL,
  `shaderStorageImageExtendedFormats` int(11) DEFAULT NULL,
  `shaderStorageImageMultisample` int(11) DEFAULT NULL,
  `shaderStorageImageReadWithoutFormat` int(11) DEFAULT NULL,
  `shaderStorageImageWriteWithoutFormat` int(11) DEFAULT NULL,
  `shaderTessellationAndGeometryPointSize` int(11) DEFAULT NULL,
  `shaderUniformBufferArrayDynamicIndexing` int(11) DEFAULT NULL,
  `sparseBinding` int(11) DEFAULT NULL,
  `sparseResidency16Samples` int(11) DEFAULT NULL,
  `sparseResidency2Samples` int(11) DEFAULT NULL,
  `sparseResidency4Samples` int(11) DEFAULT NULL,
  `sparseResidency8Samples` int(11) DEFAULT NULL,
  `sparseResidencyAliased` int(11) DEFAULT NULL,
  `sparseResidencyBuffer` int(11) DEFAULT NULL,
  `sparseResidencyImage2D` int(11) DEFAULT NULL,
  `sparseResidencyImage3D` int(11) DEFAULT NULL,
  `tessellationShader` int(11) DEFAULT NULL,
  `textureCompressionASTC_LDR` int(11) DEFAULT NULL,
  `textureCompressionBC` int(11) DEFAULT NULL,
  `textureCompressionETC2` int(11) DEFAULT NULL,
  `variableMultisampleRate` int(11) DEFAULT NULL,
  `vertexPipelineStoresAndAtomics` int(11) DEFAULT NULL,
  `wideLines` int(11) DEFAULT NULL,
  PRIMARY KEY (`reportid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `deviceformats` (
  `reportid` int(11) NOT NULL,
  `formatid` int(11) NOT NULL,
  `lineartilingfeatures` int(11) DEFAULT NULL,
  `optimaltilingfeatures` int(11) DEFAULT NULL,
  `supported` int(11) DEFAULT NULL,
  `bufferfeatures` int(11) DEFAULT NULL,
  PRIMARY KEY (`reportid`,`formatid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `devicelayerextensions` (
  `reportid` int(11) NOT NULL,
  `devicelayerid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `specversion` int(11) DEFAULT NULL,
  PRIMARY KEY (`reportid`,`devicelayerid`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `devicelayers` (
  `reportid` int(11) NOT NULL,
  `layerid` int(11) NOT NULL,
  `implversion` tinytext,
  `specversion` tinytext,
  PRIMARY KEY (`reportid`,`layerid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `devicelimits` (
  `reportid` int(11) NOT NULL,
  `bufferImageGranularity` float DEFAULT NULL,
  `discreteQueuePriorities` float DEFAULT NULL,
  `framebufferColorSampleCounts` float DEFAULT NULL,
  `framebufferDepthSampleCounts` float DEFAULT NULL,
  `framebufferNoAttachmentsSampleCounts` float DEFAULT NULL,
  `framebufferStencilSampleCounts` float DEFAULT NULL,
  `lineWidthGranularity` float DEFAULT NULL,
  `lineWidthRange[0]` float DEFAULT NULL,
  `lineWidthRange[1]` float DEFAULT NULL,
  `maxBoundDescriptorSets` float DEFAULT NULL,
  `maxClipDistances` float DEFAULT NULL,
  `maxColorAttachments` float DEFAULT NULL,
  `maxCombinedClipAndCullDistances` float DEFAULT NULL,
  `maxComputeSharedMemorySize` float DEFAULT NULL,
  `maxComputeWorkGroupCount[0]` float DEFAULT NULL,
  `maxComputeWorkGroupCount[1]` float DEFAULT NULL,
  `maxComputeWorkGroupCount[2]` float DEFAULT NULL,
  `maxComputeWorkGroupInvocations` float DEFAULT NULL,
  `maxComputeWorkGroupSize[0]` float DEFAULT NULL,
  `maxComputeWorkGroupSize[1]` float DEFAULT NULL,
  `maxComputeWorkGroupSize[2]` float DEFAULT NULL,
  `maxCullDistances` float DEFAULT NULL,
  `maxDescriptorSetInputAttachments` float DEFAULT NULL,
  `maxDescriptorSetSampledImages` float DEFAULT NULL,
  `maxDescriptorSetSamplers` float DEFAULT NULL,
  `maxDescriptorSetStorageBuffers` float DEFAULT NULL,
  `maxDescriptorSetStorageBuffersDynamic` float DEFAULT NULL,
  `maxDescriptorSetStorageImages` float DEFAULT NULL,
  `maxDescriptorSetUniformBuffers` float DEFAULT NULL,
  `maxDescriptorSetUniformBuffersDynamic` float DEFAULT NULL,
  `maxDrawIndexedIndexValue` float DEFAULT NULL,
  `maxDrawIndirectCount` float DEFAULT NULL,
  `maxFragmentCombinedOutputResources` float DEFAULT NULL,
  `maxFragmentDualSrcAttachments` float DEFAULT NULL,
  `maxFragmentInputComponents` float DEFAULT NULL,
  `maxFragmentOutputAttachments` float DEFAULT NULL,
  `maxFramebufferHeight` float DEFAULT NULL,
  `maxFramebufferLayers` float DEFAULT NULL,
  `maxFramebufferWidth` float DEFAULT NULL,
  `maxGeometryInputComponents` float DEFAULT NULL,
  `maxGeometryOutputComponents` float DEFAULT NULL,
  `maxGeometryOutputVertices` float DEFAULT NULL,
  `maxGeometryShaderInvocations` float DEFAULT NULL,
  `maxGeometryTotalOutputComponents` float DEFAULT NULL,
  `maxImageArrayLayers` float DEFAULT NULL,
  `maxImageDimension1D` float DEFAULT NULL,
  `maxImageDimension2D` float DEFAULT NULL,
  `maxImageDimension3D` float DEFAULT NULL,
  `maxImageDimensionCube` float DEFAULT NULL,
  `maxInterpolationOffset` float DEFAULT NULL,
  `maxMemoryAllocationCount` float DEFAULT NULL,
  `maxPerStageDescriptorInputAttachments` float DEFAULT NULL,
  `maxPerStageDescriptorSampledImages` float DEFAULT NULL,
  `maxPerStageDescriptorSamplers` float DEFAULT NULL,
  `maxPerStageDescriptorStorageBuffers` float DEFAULT NULL,
  `maxPerStageDescriptorStorageImages` float DEFAULT NULL,
  `maxPerStageDescriptorUniformBuffers` float DEFAULT NULL,
  `maxPerStageResources` float DEFAULT NULL,
  `maxPushConstantsSize` float DEFAULT NULL,
  `maxSampleMaskWords` float DEFAULT NULL,
  `maxSamplerAllocationCount` float DEFAULT NULL,
  `maxSamplerAnisotropy` float DEFAULT NULL,
  `maxSamplerLodBias` float DEFAULT NULL,
  `maxStorageBufferRange` float DEFAULT NULL,
  `maxTessellationControlPerPatchOutputComponents` float DEFAULT NULL,
  `maxTessellationControlPerVertexInputComponents` float DEFAULT NULL,
  `maxTessellationControlPerVertexOutputComponents` float DEFAULT NULL,
  `maxTessellationControlTotalOutputComponents` float DEFAULT NULL,
  `maxTessellationEvaluationInputComponents` float DEFAULT NULL,
  `maxTessellationEvaluationOutputComponents` float DEFAULT NULL,
  `maxTessellationGenerationLevel` float DEFAULT NULL,
  `maxTessellationPatchSize` float DEFAULT NULL,
  `maxTexelBufferElements` float DEFAULT NULL,
  `maxTexelGatherOffset` float DEFAULT NULL,
  `maxTexelOffset` float DEFAULT NULL,
  `maxUniformBufferRange` float DEFAULT NULL,
  `maxVertexInputAttributeOffset` float DEFAULT NULL,
  `maxVertexInputAttributes` float DEFAULT NULL,
  `maxVertexInputBindingStride` float DEFAULT NULL,
  `maxVertexInputBindings` float DEFAULT NULL,
  `maxVertexOutputComponents` float DEFAULT NULL,
  `maxViewportDimensions[0]` float DEFAULT NULL,
  `maxViewportDimensions[1]` float DEFAULT NULL,
  `maxViewports` float DEFAULT NULL,
  `minInterpolationOffset` float DEFAULT NULL,
  `minMemoryMapAlignment` float DEFAULT NULL,
  `minStorageBufferOffsetAlignment` float DEFAULT NULL,
  `minTexelBufferOffsetAlignment` float DEFAULT NULL,
  `minTexelGatherOffset` float DEFAULT NULL,
  `minTexelOffset` float DEFAULT NULL,
  `minUniformBufferOffsetAlignment` float DEFAULT NULL,
  `mipmapPrecisionBits` float DEFAULT NULL,
  `nonCoherentAtomSize` float DEFAULT NULL,
  `optimalBufferCopyOffsetAlignment` float DEFAULT NULL,
  `optimalBufferCopyRowPitchAlignment` float DEFAULT NULL,
  `pointSizeGranularity` float DEFAULT NULL,
  `pointSizeRange[0]` float DEFAULT NULL,
  `pointSizeRange[1]` float DEFAULT NULL,
  `sampledImageColorSampleCounts` float DEFAULT NULL,
  `sampledImageDepthSampleCounts` float DEFAULT NULL,
  `sampledImageIntegerSampleCounts` float DEFAULT NULL,
  `sampledImageStencilSampleCounts` float DEFAULT NULL,
  `sparseAddressSpaceSize` float DEFAULT NULL,
  `standardSampleLocations` float DEFAULT NULL,
  `storageImageSampleCounts` float DEFAULT NULL,
  `strictLines` float DEFAULT NULL,
  `subPixelInterpolationOffsetBits` float DEFAULT NULL,
  `subPixelPrecisionBits` float DEFAULT NULL,
  `subTexelPrecisionBits` float DEFAULT NULL,
  `timestampComputeAndGraphics` float DEFAULT NULL,
  `timestampPeriod` float DEFAULT NULL,
  `viewportBoundsRange[0]` float DEFAULT NULL,
  `viewportBoundsRange[1]` float DEFAULT NULL,
  `viewportSubPixelBits` float DEFAULT NULL,
  PRIMARY KEY (`reportid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `devicememoryheaps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reportid` int(11) NOT NULL,
  `flags` int(11) DEFAULT NULL,
  `size` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`,`reportid`)
) ENGINE=InnoDB AUTO_INCREMENT=1585 DEFAULT CHARSET=latin1;

CREATE TABLE `devicememorytypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reportid` int(11) NOT NULL,
  `heapindex` int(11) DEFAULT NULL,
  `propertyflags` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`,`reportid`)
) ENGINE=InnoDB AUTO_INCREMENT=4342 DEFAULT CHARSET=latin1;

CREATE TABLE `deviceproperties` (
  `reportid` int(11) NOT NULL,
  `vendorid` tinytext,
  `apiversion` tinytext,
  `deviceid` tinytext,
  `devicename` tinytext,
  `devicetype` tinytext,
  `driverversion` tinytext,
  `driverversionraw` int(11) DEFAULT NULL,
  `residencyAlignedMipSize` int(11) DEFAULT NULL,
  `residencyNonResidentStrict` int(11) DEFAULT NULL,
  `residencyStandard2DBlockShape` int(11) DEFAULT NULL,
  `residencyStandard2DMSBlockShape` int(11) DEFAULT NULL,
  `residencyStandard3DBlockShape` int(11) DEFAULT NULL,
  PRIMARY KEY (`reportid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `devicequeues` (
  `reportid` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `count` int(11) DEFAULT NULL,
  `flags` int(11) DEFAULT NULL,
  `timestampValidBits` int(11) DEFAULT NULL,
  `minImageTransferGranularity.width` int(11) DEFAULT NULL,
  `minImageTransferGranularity.height` int(11) DEFAULT NULL,
  `minImageTransferGranularity.depth` int(11) DEFAULT NULL,
  PRIMARY KEY (`reportid`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `extensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1161 DEFAULT CHARSET=latin1;

CREATE TABLE `layerextensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `layerid` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`,`layerid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `layers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1530 DEFAULT CHARSET=latin1;

CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submissiondate` datetime DEFAULT CURRENT_TIMESTAMP,
  `submitter` tinytext,
  `devicename` varchar(45) NOT NULL,
  `driverversion` char(32) NOT NULL,
  `apiversion` char(32) DEFAULT NULL,
  `counter` int(11) DEFAULT NULL,
  `osarchitecture` tinytext,
  `osname` tinytext,
  `osversion` tinytext,
  `description` varchar(255) DEFAULT NULL,
  `version` char(16) DEFAULT NULL,
  `headerversion` char(16) DEFAULT NULL,
  PRIMARY KEY (`id`,`devicename`,`driverversion`)
) ENGINE=InnoDB AUTO_INCREMENT=824 DEFAULT CHARSET=latin1;

CREATE TABLE `reportsjson` (
  `reportid` int(11) NOT NULL,
  `json` mediumtext NOT NULL,
  PRIMARY KEY (`reportid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `vendorids` (
  `id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `VkFormat` (
  `value` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`value`),
  UNIQUE KEY `value_UNIQUE` (`value`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `VkPhysicalDeviceType` (
  `value` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
