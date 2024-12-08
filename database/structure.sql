SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DELIMITER $$
CREATE  PROCEDURE `delete_report` (IN `inReportid` INT)   BEGIN

	start transaction;
	delete from `deviceextensions` where reportID = inReportid;
	delete from `devicefeatures` where reportID = inReportid;
	delete from `devicefeatures11` where reportID = inReportid;    
	delete from `devicefeatures12` where reportID = inReportid;    
	delete from `devicefeatures13` where reportID = inReportid;    
	delete from `devicefeatures14` where reportID = inReportid;    
	delete from `devicefeatures2` where reportID = inReportid;
	delete from `deviceformats` where reportID = inReportid;
	delete from `devicelayerextensions` where reportID = inReportid;
	delete from `devicelayers` where reportID = inReportid;
	delete from `devicelimits` where reportID = inReportid;
	delete from `devicememoryheaps` where reportid = inReportid;
	delete from `devicememorytypes` where reportid = inReportid;
	delete from `deviceplatformdetails` where reportid = inReportid;
	delete from `deviceproperties` where reportid = inReportid;
	delete from `deviceproperties11` where reportid = inReportid;
	delete from `deviceproperties12` where reportid = inReportid;
	delete from `deviceproperties13` where reportid = inReportid;
	delete from `deviceproperties14` where reportid = inReportid;
	delete from `deviceproperties2` where reportid = inReportid;
	delete from `devicequeues` where reportid = inReportid;
	delete from `devicesurfacecapabilities` where reportid = inReportid;
	delete from `devicesurfacemodes` where reportid = inReportid;
    delete from `deviceprofiles` where reportid = inReportid;
	delete from `reportsjson` where reportid = inReportid;
	delete from `reports` where id = inReportid;
	commit;
END$$

CREATE  PROCEDURE `truncate_all` ()   BEGIN
    
END$$

CREATE  FUNCTION `VendorId` (`val` INTEGER) RETURNS CHAR(255) CHARSET latin1 COLLATE latin1_swedish_ci  BEGIN
	DECLARE res char(255);
	SELECT name from vendorids where id = val into res;
    select ifnull(res, HEX(val)) into res;
	return res;
END$$

CREATE  FUNCTION `VkFormat` (`val` INTEGER) RETURNS CHAR(255) CHARSET utf8mb3 COLLATE utf8mb3_general_ci  BEGIN
	DECLARE res char(255);
	SELECT name from VkFormat where value = val into res;
	
	IF (res = '') THEN
		RETURN 'unknown';
	ELSE
		RETURN res;
	END IF;

END$$

CREATE  FUNCTION `VkPhysicalDeviceType` (`val` INTEGER) RETURNS CHAR(255) CHARSET utf8mb3 COLLATE utf8mb3_general_ci  BEGIN
	DECLARE res char(255);
	SELECT name from VkPhysicalDeviceType where value = val into res;
	
	IF (res = '') THEN
		RETURN 'unknown';
	ELSE
		RETURN res;
	END IF;

END$$

CREATE  FUNCTION `VkVersion` (`val` BIGINT) RETURNS TEXT CHARSET latin1 COLLATE latin1_swedish_ci  BEGIN
RETURN CONCAT(cast(val >> 22 as char), ".", cast((val >> 12) & 1023 as char), "." , cast(val & 4095 as char));
END$$

DELIMITER ;

CREATE TABLE `blacklist` (
  `devicename` char(255) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicealiases` (
  `devicename` varchar(255) NOT NULL,
  `alias` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceextensions` (
  `reportid` int(11) NOT NULL,
  `extensionid` int(11) NOT NULL,
  `specversion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
DELIMITER $$
CREATE TRIGGER `deviceextensions_AFTER_INSERT` AFTER INSERT ON `deviceextensions` FOR EACH ROW begin
	declare reportostype int default -1;
    select ostype into reportostype from reports where id = new.reportid;
    
    if reportostype = 0 then
		
        update extensions set datewindows = now() where id = new.extensionid and datewindows is null;
    elseif reportostype = 1 then
		
        update extensions set datelinux = now() where id = new.extensionid and datelinux is null;
    elseif reportostype = 2 then
		
        update extensions set dateandroid = now() where id = new.extensionid and dateandroid is null;
    elseif reportostype = 3 then 
		
        update extensions set datemacos = now() where id = new.extensionid and datemacos is null;
	elseif reportostype = 4 then
		
        update extensions set dateios = now() where id = new.extensionid and dateios is null;
    end if;
end
$$
DELIMITER ;

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
  `wideLines` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicefeatures2` (
  `reportid` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `supported` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicefeatures11` (
  `reportid` int(11) NOT NULL,
  `storageBuffer16BitAccess` int(11) DEFAULT NULL,
  `uniformAndStorageBuffer16BitAccess` int(11) DEFAULT NULL,
  `storagePushConstant16` int(11) DEFAULT NULL,
  `storageInputOutput16` int(11) DEFAULT NULL,
  `multiview` int(11) DEFAULT NULL,
  `multiviewGeometryShader` int(11) DEFAULT NULL,
  `multiviewTessellationShader` int(11) DEFAULT NULL,
  `variablePointersStorageBuffer` int(11) DEFAULT NULL,
  `variablePointers` int(11) DEFAULT NULL,
  `protectedMemory` int(11) DEFAULT NULL,
  `samplerYcbcrConversion` int(11) DEFAULT NULL,
  `shaderDrawParameters` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicefeatures12` (
  `reportid` int(11) NOT NULL,
  `samplerMirrorClampToEdge` int(11) DEFAULT NULL,
  `drawIndirectCount` int(11) DEFAULT NULL,
  `storageBuffer8BitAccess` int(11) DEFAULT NULL,
  `uniformAndStorageBuffer8BitAccess` int(11) DEFAULT NULL,
  `storagePushConstant8` int(11) DEFAULT NULL,
  `shaderBufferInt64Atomics` int(11) DEFAULT NULL,
  `shaderSharedInt64Atomics` int(11) DEFAULT NULL,
  `shaderFloat16` int(11) DEFAULT NULL,
  `shaderInt8` int(11) DEFAULT NULL,
  `descriptorIndexing` int(11) DEFAULT NULL,
  `shaderInputAttachmentArrayDynamicIndexing` int(11) DEFAULT NULL,
  `shaderUniformTexelBufferArrayDynamicIndexing` int(11) DEFAULT NULL,
  `shaderStorageTexelBufferArrayDynamicIndexing` int(11) DEFAULT NULL,
  `shaderUniformBufferArrayNonUniformIndexing` int(11) DEFAULT NULL,
  `shaderSampledImageArrayNonUniformIndexing` int(11) DEFAULT NULL,
  `shaderStorageBufferArrayNonUniformIndexing` int(11) DEFAULT NULL,
  `shaderStorageImageArrayNonUniformIndexing` int(11) DEFAULT NULL,
  `shaderInputAttachmentArrayNonUniformIndexing` int(11) DEFAULT NULL,
  `shaderUniformTexelBufferArrayNonUniformIndexing` int(11) DEFAULT NULL,
  `shaderStorageTexelBufferArrayNonUniformIndexing` int(11) DEFAULT NULL,
  `descriptorBindingUniformBufferUpdateAfterBind` int(11) DEFAULT NULL,
  `descriptorBindingSampledImageUpdateAfterBind` int(11) DEFAULT NULL,
  `descriptorBindingStorageImageUpdateAfterBind` int(11) DEFAULT NULL,
  `descriptorBindingStorageBufferUpdateAfterBind` int(11) DEFAULT NULL,
  `descriptorBindingUniformTexelBufferUpdateAfterBind` int(11) DEFAULT NULL,
  `descriptorBindingStorageTexelBufferUpdateAfterBind` int(11) DEFAULT NULL,
  `descriptorBindingUpdateUnusedWhilePending` int(11) DEFAULT NULL,
  `descriptorBindingPartiallyBound` int(11) DEFAULT NULL,
  `descriptorBindingVariableDescriptorCount` int(11) DEFAULT NULL,
  `runtimeDescriptorArray` int(11) DEFAULT NULL,
  `samplerFilterMinmax` int(11) DEFAULT NULL,
  `scalarBlockLayout` int(11) DEFAULT NULL,
  `imagelessFramebuffer` int(11) DEFAULT NULL,
  `uniformBufferStandardLayout` int(11) DEFAULT NULL,
  `shaderSubgroupExtendedTypes` int(11) DEFAULT NULL,
  `separateDepthStencilLayouts` int(11) DEFAULT NULL,
  `hostQueryReset` int(11) DEFAULT NULL,
  `timelineSemaphore` int(11) DEFAULT NULL,
  `bufferDeviceAddress` int(11) DEFAULT NULL,
  `bufferDeviceAddressCaptureReplay` int(11) DEFAULT NULL,
  `bufferDeviceAddressMultiDevice` int(11) DEFAULT NULL,
  `vulkanMemoryModel` int(11) DEFAULT NULL,
  `vulkanMemoryModelDeviceScope` int(11) DEFAULT NULL,
  `vulkanMemoryModelAvailabilityVisibilityChains` int(11) DEFAULT NULL,
  `shaderOutputViewportIndex` int(11) DEFAULT NULL,
  `shaderOutputLayer` int(11) DEFAULT NULL,
  `subgroupBroadcastDynamicId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicefeatures13` (
  `reportid` int(11) NOT NULL,
  `robustImageAccess` int(11) DEFAULT NULL,
  `inlineUniformBlock` int(11) DEFAULT NULL,
  `descriptorBindingInlineUniformBlockUpdateAfterBind` int(11) DEFAULT NULL,
  `pipelineCreationCacheControl` int(11) DEFAULT NULL,
  `privateData` int(11) DEFAULT NULL,
  `shaderDemoteToHelperInvocation` int(11) DEFAULT NULL,
  `shaderTerminateInvocation` int(11) DEFAULT NULL,
  `subgroupSizeControl` int(11) DEFAULT NULL,
  `computeFullSubgroups` int(11) DEFAULT NULL,
  `synchronization2` int(11) DEFAULT NULL,
  `textureCompressionASTC_HDR` int(11) DEFAULT NULL,
  `shaderZeroInitializeWorkgroupMemory` int(11) DEFAULT NULL,
  `dynamicRendering` int(11) DEFAULT NULL,
  `shaderIntegerDotProduct` int(11) DEFAULT NULL,
  `maintenance4` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicefeatures14` (
  `reportid` int(11) NOT NULL,
  `globalPriorityQuery` int(11) DEFAULT NULL,
  `shaderSubgroupRotate` int(11) DEFAULT NULL,
  `shaderSubgroupRotateClustered` int(11) DEFAULT NULL,
  `shaderFloatControls2` int(11) DEFAULT NULL,
  `shaderExpectAssume` int(11) DEFAULT NULL,
  `rectangularLines` int(11) DEFAULT NULL,
  `bresenhamLines` int(11) DEFAULT NULL,
  `smoothLines` int(11) DEFAULT NULL,
  `stippledRectangularLines` int(11) DEFAULT NULL,
  `stippledBresenhamLines` int(11) DEFAULT NULL,
  `stippledSmoothLines` int(11) DEFAULT NULL,
  `vertexAttributeInstanceRateDivisor` int(11) DEFAULT NULL,
  `vertexAttributeInstanceRateZeroDivisor` int(11) DEFAULT NULL,
  `indexTypeUint8` int(11) DEFAULT NULL,
  `dynamicRenderingLocalRead` int(11) DEFAULT NULL,
  `maintenance5` int(11) DEFAULT NULL,
  `maintenance6` int(11) DEFAULT NULL,
  `pipelineProtectedAccess` int(11) DEFAULT NULL,
  `pipelineRobustness` int(11) DEFAULT NULL,
  `hostImageCopy` int(11) DEFAULT NULL,
  `pushDescriptor` int(11) DEFAULT NULL,
  PRIMARY KEY (`reportid`),
  CONSTRAINT `devicefeatures14_report` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceformats` (
  `reportid` int(11) NOT NULL,
  `formatid` int(11) NOT NULL,
  `lineartilingfeatures` bigint(20) DEFAULT NULL,
  `optimaltilingfeatures` bigint(20) DEFAULT NULL,
  `supported` int(11) DEFAULT NULL,
  `bufferfeatures` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceinstanceextensions` (
  `reportid` int(11) NOT NULL,
  `extensionid` int(11) NOT NULL,
  `specversion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceinstancelayerextensions` (
  `reportid` int(11) NOT NULL,
  `devicelayerid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `specversion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceinstancelayers` (
  `reportid` int(11) NOT NULL,
  `layerid` int(11) NOT NULL,
  `implversion` tinytext DEFAULT NULL,
  `specversion` tinytext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicelayerextensions` (
  `reportid` int(11) NOT NULL,
  `devicelayerid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `specversion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicelayers` (
  `reportid` int(11) NOT NULL,
  `layerid` int(11) NOT NULL,
  `implversion` tinytext DEFAULT NULL,
  `specversion` tinytext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicelimits` (
  `reportid` int(11) NOT NULL,
  `bufferImageGranularity` float DEFAULT NULL,
  `discreteQueuePriorities` int(11) UNSIGNED DEFAULT NULL,
  `framebufferColorSampleCounts` float DEFAULT NULL,
  `framebufferDepthSampleCounts` float DEFAULT NULL,
  `framebufferNoAttachmentsSampleCounts` float DEFAULT NULL,
  `framebufferStencilSampleCounts` float DEFAULT NULL,
  `lineWidthGranularity` float DEFAULT NULL,
  `lineWidthRange[0]` float DEFAULT NULL,
  `lineWidthRange[1]` float DEFAULT NULL,
  `maxBoundDescriptorSets` int(11) UNSIGNED DEFAULT NULL,
  `maxClipDistances` int(11) UNSIGNED DEFAULT NULL,
  `maxColorAttachments` int(11) UNSIGNED DEFAULT NULL,
  `maxCombinedClipAndCullDistances` int(11) UNSIGNED DEFAULT NULL,
  `maxComputeSharedMemorySize` int(11) UNSIGNED DEFAULT NULL,
  `maxComputeWorkGroupCount[0]` int(11) UNSIGNED DEFAULT NULL,
  `maxComputeWorkGroupCount[1]` int(11) UNSIGNED DEFAULT NULL,
  `maxComputeWorkGroupCount[2]` int(11) UNSIGNED DEFAULT NULL,
  `maxComputeWorkGroupInvocations` int(11) UNSIGNED DEFAULT NULL,
  `maxComputeWorkGroupSize[0]` int(11) UNSIGNED DEFAULT NULL,
  `maxComputeWorkGroupSize[1]` int(11) UNSIGNED DEFAULT NULL,
  `maxComputeWorkGroupSize[2]` int(11) UNSIGNED DEFAULT NULL,
  `maxCullDistances` int(11) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetInputAttachments` int(11) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetSampledImages` int(11) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetSamplers` int(11) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetStorageBuffers` int(11) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetStorageBuffersDynamic` int(11) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetStorageImages` int(11) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUniformBuffers` int(11) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUniformBuffersDynamic` int(11) UNSIGNED DEFAULT NULL,
  `maxDrawIndexedIndexValue` int(11) UNSIGNED DEFAULT NULL,
  `maxDrawIndirectCount` int(11) UNSIGNED DEFAULT NULL,
  `maxFragmentCombinedOutputResources` int(11) UNSIGNED DEFAULT NULL,
  `maxFragmentDualSrcAttachments` int(11) UNSIGNED DEFAULT NULL,
  `maxFragmentInputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxFragmentOutputAttachments` int(11) UNSIGNED DEFAULT NULL,
  `maxFramebufferHeight` int(11) UNSIGNED DEFAULT NULL,
  `maxFramebufferLayers` int(11) UNSIGNED DEFAULT NULL,
  `maxFramebufferWidth` int(11) UNSIGNED DEFAULT NULL,
  `maxGeometryInputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxGeometryOutputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxGeometryOutputVertices` int(11) UNSIGNED DEFAULT NULL,
  `maxGeometryShaderInvocations` int(11) UNSIGNED DEFAULT NULL,
  `maxGeometryTotalOutputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxImageArrayLayers` int(11) UNSIGNED DEFAULT NULL,
  `maxImageDimension1D` int(11) UNSIGNED DEFAULT NULL,
  `maxImageDimension2D` int(11) UNSIGNED DEFAULT NULL,
  `maxImageDimension3D` int(11) UNSIGNED DEFAULT NULL,
  `maxImageDimensionCube` int(11) UNSIGNED DEFAULT NULL,
  `maxInterpolationOffset` float DEFAULT NULL,
  `maxMemoryAllocationCount` int(11) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorInputAttachments` int(11) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorSampledImages` int(11) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorSamplers` int(11) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorStorageBuffers` int(11) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorStorageImages` int(11) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorUniformBuffers` int(11) UNSIGNED DEFAULT NULL,
  `maxPerStageResources` int(11) UNSIGNED DEFAULT NULL,
  `maxPushConstantsSize` int(11) UNSIGNED DEFAULT NULL,
  `maxSampleMaskWords` int(11) UNSIGNED DEFAULT NULL,
  `maxSamplerAllocationCount` int(11) UNSIGNED DEFAULT NULL,
  `maxSamplerAnisotropy` float DEFAULT NULL,
  `maxSamplerLodBias` float DEFAULT NULL,
  `maxStorageBufferRange` int(11) UNSIGNED DEFAULT NULL,
  `maxTessellationControlPerPatchOutputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxTessellationControlPerVertexInputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxTessellationControlPerVertexOutputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxTessellationControlTotalOutputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxTessellationEvaluationInputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxTessellationEvaluationOutputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxTessellationGenerationLevel` int(11) UNSIGNED DEFAULT NULL,
  `maxTessellationPatchSize` int(11) UNSIGNED DEFAULT NULL,
  `maxTexelBufferElements` int(11) UNSIGNED DEFAULT NULL,
  `maxTexelGatherOffset` int(11) UNSIGNED DEFAULT NULL,
  `maxTexelOffset` int(11) UNSIGNED DEFAULT NULL,
  `maxUniformBufferRange` int(11) UNSIGNED DEFAULT NULL,
  `maxVertexInputAttributeOffset` int(11) UNSIGNED DEFAULT NULL,
  `maxVertexInputAttributes` int(11) UNSIGNED DEFAULT NULL,
  `maxVertexInputBindingStride` int(11) UNSIGNED DEFAULT NULL,
  `maxVertexInputBindings` int(11) UNSIGNED DEFAULT NULL,
  `maxVertexOutputComponents` int(11) UNSIGNED DEFAULT NULL,
  `maxViewportDimensions[0]` float DEFAULT NULL,
  `maxViewportDimensions[1]` float DEFAULT NULL,
  `maxViewports` int(11) UNSIGNED DEFAULT NULL,
  `minInterpolationOffset` float DEFAULT NULL,
  `minMemoryMapAlignment` float DEFAULT NULL,
  `minStorageBufferOffsetAlignment` float DEFAULT NULL,
  `minTexelBufferOffsetAlignment` float DEFAULT NULL,
  `minTexelGatherOffset` int(11) DEFAULT NULL,
  `minTexelOffset` int(11) DEFAULT NULL,
  `minUniformBufferOffsetAlignment` float DEFAULT NULL,
  `mipmapPrecisionBits` int(11) UNSIGNED DEFAULT NULL,
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
  `sparseAddressSpaceSize` bigint(20) UNSIGNED DEFAULT NULL,
  `standardSampleLocations` float DEFAULT NULL,
  `storageImageSampleCounts` float DEFAULT NULL,
  `strictLines` float DEFAULT NULL,
  `subPixelInterpolationOffsetBits` int(11) UNSIGNED DEFAULT NULL,
  `subPixelPrecisionBits` int(11) UNSIGNED DEFAULT NULL,
  `subTexelPrecisionBits` int(11) UNSIGNED DEFAULT NULL,
  `timestampComputeAndGraphics` float DEFAULT NULL,
  `timestampPeriod` float DEFAULT NULL,
  `viewportBoundsRange[0]` float DEFAULT NULL,
  `viewportBoundsRange[1]` float DEFAULT NULL,
  `viewportSubPixelBits` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicememoryheaps` (
  `id` int(11) NOT NULL,
  `reportid` int(11) NOT NULL,
  `flags` int(11) DEFAULT NULL,
  `size` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicememorytypes` (
  `id` int(11) NOT NULL,
  `reportid` int(11) NOT NULL,
  `heapindex` int(11) DEFAULT NULL,
  `propertyflags` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceplatformdetails` (
  `reportid` int(11) NOT NULL,
  `platformdetailid` int(11) NOT NULL,
  `value` tinytext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
DELIMITER $$
CREATE TRIGGER `deviceplatformdetails_AFTER_INSERT` AFTER INSERT ON `deviceplatformdetails` FOR EACH ROW BEGIN
	
	IF NEW.platformdetailid = (select id from platformdetails where name = 'android.ProductModel') THEN 
		SET @marketingname = (select marketingname from googledevicelist where model = trim(new.value) limit 1);
        SET @manufacturer = (select retailbranding from googledevicelist where model = trim(new.value) limit 1);
        IF @marketingname IS NOT NULL THEN
			UPDATE reports SET displayname = trim(concat(@manufacturer, ' ', @marketingname)) WHERE id = NEW.reportid;
        END IF;
    END IF;
END
$$
DELIMITER ;

CREATE TABLE `deviceprofiles` (
  `reportid` int(11) NOT NULL,
  `profileid` int(11) NOT NULL,
  `specversion` int(11) DEFAULT NULL,
  `supported` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceproperties` (
  `reportid` int(11) NOT NULL,
  `vendorid` tinytext DEFAULT NULL,
  `apiversion` tinytext DEFAULT NULL,
  `apiversionraw` int(11) DEFAULT NULL,
  `deviceid` tinytext DEFAULT NULL,
  `devicename` varchar(200) DEFAULT NULL,
  `devicetype` tinytext DEFAULT NULL,
  `driverversion` tinytext DEFAULT NULL,
  `driverversionraw` bigint(20) DEFAULT NULL,
  `residencyAlignedMipSize` int(11) DEFAULT NULL,
  `residencyNonResidentStrict` int(11) DEFAULT NULL,
  `residencyStandard2DBlockShape` int(11) DEFAULT NULL,
  `residencyStandard2DMultisampleBlockShape` int(11) DEFAULT NULL,
  `residencyStandard3DBlockShape` int(11) DEFAULT NULL,
  `headerversion` tinytext DEFAULT NULL,
  `productModel` tinytext DEFAULT NULL,
  `productManufacturer` tinytext DEFAULT NULL,
  `pipelineCacheUUID` text DEFAULT NULL,
  `subgroupProperties.subgroupSize` int(11) DEFAULT NULL,
  `subgroupProperties.supportedStages` int(11) DEFAULT NULL,
  `subgroupProperties.supportedOperations` int(11) DEFAULT NULL,
  `subgroupProperties.quadOperationsInAllStages` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceproperties2` (
  `reportid` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `value` varchar(1024) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceproperties11` (
  `reportid` int(11) NOT NULL,
  `deviceUUID` text DEFAULT NULL,
  `driverUUID` text DEFAULT NULL,
  `deviceLUID` text DEFAULT NULL,
  `deviceNodeMask` int(10) UNSIGNED DEFAULT NULL,
  `deviceLUIDValid` tinyint(1) DEFAULT NULL,
  `subgroupSize` int(10) UNSIGNED DEFAULT NULL,
  `subgroupSupportedStages` int(11) DEFAULT NULL,
  `subgroupSupportedOperations` int(11) DEFAULT NULL,
  `subgroupQuadOperationsInAllStages` tinyint(1) DEFAULT NULL,
  `pointClippingBehavior` int(11) DEFAULT NULL,
  `maxMultiviewViewCount` int(10) UNSIGNED DEFAULT NULL,
  `maxMultiviewInstanceIndex` int(10) UNSIGNED DEFAULT NULL,
  `protectedNoFault` tinyint(1) DEFAULT NULL,
  `maxPerSetDescriptors` int(10) UNSIGNED DEFAULT NULL,
  `maxMemoryAllocationSize` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceproperties12` (
  `reportid` int(11) NOT NULL,
  `driverID` int(11) DEFAULT NULL,
  `driverName` text DEFAULT NULL,
  `driverInfo` text DEFAULT NULL,
  `conformanceVersion` text DEFAULT NULL,
  `denormBehaviorIndependence` int(11) DEFAULT NULL,
  `roundingModeIndependence` int(11) DEFAULT NULL,
  `shaderSignedZeroInfNanPreserveFloat16` tinyint(1) DEFAULT NULL,
  `shaderSignedZeroInfNanPreserveFloat32` tinyint(1) DEFAULT NULL,
  `shaderSignedZeroInfNanPreserveFloat64` tinyint(1) DEFAULT NULL,
  `shaderDenormPreserveFloat16` tinyint(1) DEFAULT NULL,
  `shaderDenormPreserveFloat32` tinyint(1) DEFAULT NULL,
  `shaderDenormPreserveFloat64` tinyint(1) DEFAULT NULL,
  `shaderDenormFlushToZeroFloat16` tinyint(1) DEFAULT NULL,
  `shaderDenormFlushToZeroFloat32` tinyint(1) DEFAULT NULL,
  `shaderDenormFlushToZeroFloat64` tinyint(1) DEFAULT NULL,
  `shaderRoundingModeRTEFloat16` tinyint(1) DEFAULT NULL,
  `shaderRoundingModeRTEFloat32` tinyint(1) DEFAULT NULL,
  `shaderRoundingModeRTEFloat64` tinyint(1) DEFAULT NULL,
  `shaderRoundingModeRTZFloat16` tinyint(1) DEFAULT NULL,
  `shaderRoundingModeRTZFloat32` tinyint(1) DEFAULT NULL,
  `shaderRoundingModeRTZFloat64` tinyint(1) DEFAULT NULL,
  `maxUpdateAfterBindDescriptorsInAllPools` int(10) UNSIGNED DEFAULT NULL,
  `shaderUniformBufferArrayNonUniformIndexingNative` tinyint(1) DEFAULT NULL,
  `shaderSampledImageArrayNonUniformIndexingNative` tinyint(1) DEFAULT NULL,
  `shaderStorageBufferArrayNonUniformIndexingNative` tinyint(1) DEFAULT NULL,
  `shaderStorageImageArrayNonUniformIndexingNative` tinyint(1) DEFAULT NULL,
  `shaderInputAttachmentArrayNonUniformIndexingNative` tinyint(1) DEFAULT NULL,
  `robustBufferAccessUpdateAfterBind` tinyint(1) DEFAULT NULL,
  `quadDivergentImplicitLod` tinyint(1) DEFAULT NULL,
  `maxPerStageDescriptorUpdateAfterBindSamplers` int(10) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorUpdateAfterBindUniformBuffers` int(10) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorUpdateAfterBindStorageBuffers` int(10) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorUpdateAfterBindSampledImages` int(10) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorUpdateAfterBindStorageImages` int(10) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorUpdateAfterBindInputAttachments` int(10) UNSIGNED DEFAULT NULL,
  `maxPerStageUpdateAfterBindResources` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUpdateAfterBindSamplers` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUpdateAfterBindUniformBuffers` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUpdateAfterBindUniformBuffersDynamic` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUpdateAfterBindStorageBuffers` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUpdateAfterBindStorageBuffersDynamic` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUpdateAfterBindSampledImages` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUpdateAfterBindStorageImages` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUpdateAfterBindInputAttachments` int(10) UNSIGNED DEFAULT NULL,
  `supportedDepthResolveModes` int(11) DEFAULT NULL,
  `supportedStencilResolveModes` int(11) DEFAULT NULL,
  `independentResolveNone` tinyint(1) DEFAULT NULL,
  `independentResolve` tinyint(1) DEFAULT NULL,
  `filterMinmaxSingleComponentFormats` tinyint(1) DEFAULT NULL,
  `filterMinmaxImageComponentMapping` tinyint(1) DEFAULT NULL,
  `maxTimelineSemaphoreValueDifference` bigint(20) UNSIGNED DEFAULT NULL,
  `framebufferIntegerColorSampleCounts` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceproperties13` (
  `reportid` int(11) NOT NULL,
  `minSubgroupSize` int(10) UNSIGNED DEFAULT NULL,
  `maxSubgroupSize` int(10) UNSIGNED DEFAULT NULL,
  `maxComputeWorkgroupSubgroups` int(10) UNSIGNED DEFAULT NULL,
  `requiredSubgroupSizeStages` int(11) DEFAULT NULL,
  `maxInlineUniformBlockSize` int(10) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorInlineUniformBlocks` int(10) UNSIGNED DEFAULT NULL,
  `maxPerStageDescriptorUpdateAfterBindInlineUniformBlocks` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetInlineUniformBlocks` int(10) UNSIGNED DEFAULT NULL,
  `maxDescriptorSetUpdateAfterBindInlineUniformBlocks` int(10) UNSIGNED DEFAULT NULL,
  `maxInlineUniformTotalSize` int(10) UNSIGNED DEFAULT NULL,
  `idp8BitUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp8BitSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp8BitMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `idp4x8BitPackedUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp4x8BitPackedSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp4x8BitPackedMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `idp16BitUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp16BitSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp16BitMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `idp32BitUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp32BitSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp32BitMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `idp64BitUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp64BitSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idp64BitMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating8BitUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating8BitSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating8BitMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating4x8BitPackedUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating4x8BitPackedSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating4x8BitPackedMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating16BitUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating16BitSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating16BitMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating32BitUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating32BitSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating32BitMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating64BitUnsignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating64BitSignedAccelerated` tinyint(1) DEFAULT NULL,
  `idpAccumulatingSaturating64BitMixedSignednessAccelerated` tinyint(1) DEFAULT NULL,
  `storageTexelBufferOffsetAlignmentBytes` bigint(20) DEFAULT NULL,
  `storageTexelBufferOffsetSingleTexelAlignment` tinyint(1) DEFAULT NULL,
  `uniformTexelBufferOffsetAlignmentBytes` bigint(20) DEFAULT NULL,
  `uniformTexelBufferOffsetSingleTexelAlignment` tinyint(1) DEFAULT NULL,
  `maxBufferSize` tinytext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `deviceproperties14` (
  `reportid` int(11) NOT NULL,
  `lineSubPixelPrecisionBits` int(10) unsigned DEFAULT NULL,
  `maxVertexAttribDivisor` int(10) unsigned DEFAULT NULL,
  `supportsNonZeroFirstInstance` tinyint(1) DEFAULT NULL,
  `maxPushDescriptors` int(10) unsigned DEFAULT NULL,
  `dynamicRenderingLocalReadDepthStencilAttachments` tinyint(1) DEFAULT NULL,
  `dynamicRenderingLocalReadMultisampledAttachments` tinyint(1) DEFAULT NULL,
  `earlyFragmentMultisampleCoverageAfterSampleCounting` tinyint(1) DEFAULT NULL,
  `earlyFragmentSampleMaskTestBeforeSampleCounting` tinyint(1) DEFAULT NULL, 
  `depthStencilSwizzleOneSupport` tinyint(1) DEFAULT NULL,
  `polygonModePointSize` tinyint(1) DEFAULT NULL,
  `nonStrictSinglePixelWideLinesUseParallelogram` tinyint(1) DEFAULT NULL,
  `nonStrictWideLinesUseParallelogram` tinyint(1) DEFAULT NULL,
  `blockTexelViewCompatibleMultipleLayers` tinyint(1) DEFAULT NULL,
  `maxCombinedImageSamplerDescriptorCount` int(10) unsigned DEFAULT NULL,
  `fragmentShadingRateClampCombinerInputs` tinyint(1) DEFAULT NULL,
  `defaultRobustnessStorageBuffers` int(10) unsigned DEFAULT NULL,
  `defaultRobustnessUniformBuffers` int(10) unsigned DEFAULT NULL,
  `defaultRobustnessVertexInputs` int(10) unsigned DEFAULT NULL,
  `defaultRobustnessImages` int(10) unsigned DEFAULT NULL,
  `copySrcLayoutCount` int(10) unsigned DEFAULT NULL,
  `pCopySrcLayouts` varchar(255) DEFAULT NULL,
  `copyDstLayoutCount` int(10) unsigned DEFAULT NULL,
  `pCopyDstLayouts` varchar(255) DEFAULT NULL,
  `optimalTilingLayoutUUID` text DEFAULT NULL,
  `identicalMemoryTypeRequirements` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`reportid`),
  CONSTRAINT `deviceproperties14_report` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicequeues` (
  `reportid` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `count` int(11) DEFAULT NULL,
  `flags` int(11) DEFAULT NULL,
  `timestampValidBits` int(11) DEFAULT NULL,
  `minImageTransferGranularity.width` int(11) DEFAULT NULL,
  `minImageTransferGranularity.height` int(11) DEFAULT NULL,
  `minImageTransferGranularity.depth` int(11) DEFAULT NULL,
  `supportsPresent` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicesurfacecapabilities` (
  `reportid` int(11) NOT NULL,
  `minImageCount` int(11) DEFAULT NULL,
  `maxImageCount` int(11) DEFAULT NULL,
  `maxImageArrayLayers` int(11) DEFAULT NULL,
  `minImageExtent.width` int(11) DEFAULT NULL,
  `minImageExtent.height` int(11) DEFAULT NULL,
  `maxImageExtent.width` int(11) DEFAULT NULL,
  `maxImageExtent.height` int(11) DEFAULT NULL,
  `supportedUsageFlags` int(11) DEFAULT NULL,
  `supportedTransforms` int(11) DEFAULT NULL,
  `supportedCompositeAlpha` int(11) DEFAULT NULL,
  `surfaceExtension` tinytext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicesurfaceformats` (
  `id` int(11) NOT NULL,
  `reportid` int(11) NOT NULL,
  `format` int(11) DEFAULT NULL,
  `colorSpace` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `devicesurfacemodes` (
  `id` int(11) NOT NULL,
  `reportid` int(11) NOT NULL,
  `presentmode` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `extensions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `date` timestamp NULL DEFAULT current_timestamp(),
  `datewindows` timestamp NULL DEFAULT NULL,
  `datelinux` timestamp NULL DEFAULT NULL,
  `dateandroid` timestamp NULL DEFAULT NULL,
  `datemacos` timestamp NULL DEFAULT NULL,
  `dateios` timestamp NULL DEFAULT NULL,
  `hasfeatures` tinyint(1) DEFAULT NULL,
  `hasproperties` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `instanceextensions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `instancelayers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `layerextensions` (
  `id` int(11) NOT NULL,
  `layerid` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `layers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `limitrequirements` (
  `limitname` char(255) NOT NULL,
  `feature` char(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `page` varchar(255) NOT NULL,
  `statement` text DEFAULT NULL,
  `execution_time` decimal(19,4) DEFAULT NULL COMMENT 'In Milliseconds'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `platformdetails` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `submissiondate` datetime DEFAULT current_timestamp(),
  `submitter` tinytext DEFAULT NULL,
  `devicename` varchar(64) NOT NULL,
  `driverversion` char(32) NOT NULL,
  `apiversion` char(32) DEFAULT NULL,
  `counter` int(11) DEFAULT NULL,
  `osarchitecture` tinytext DEFAULT NULL,
  `osname` char(64) DEFAULT NULL,
  `osversion` tinytext DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `version` char(16) DEFAULT NULL,
  `headerversion` char(16) DEFAULT NULL,
  `displayname` varchar(128) DEFAULT NULL,
  `ostype` int(11) DEFAULT NULL,
  `internalid` char(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
DELIMITER $$
CREATE TRIGGER `reports_BEFORE_INSERT` BEFORE INSERT ON `reports` FOR EACH ROW begin
	IF NEW.osname != 'android' THEN
		SET NEW.displayname = NEW.devicename;
    END IF;
    if new.osname = 'windows' then 
		set new.ostype = 0;
	else
		if new.osname = 'android' then
			set new.ostype = 2;
		else
			if new.osname = 'osx' then
				set new.ostype = 3;
			else
				if new.osname = 'ios' then
					set new.ostype = 4;
				else
					if new.osname = 'unknown' then
						set new.ostype = -1;
					else
						set new.ostype = 1;
					end if;
				end if;
			end if;
		end if;
	end if;
end
$$
DELIMITER ;

CREATE TABLE `reportsjson` (
  `reportid` int(11) NOT NULL,
  `json` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `reportupdatehistory` (
  `id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `submitter` varchar(255) DEFAULT NULL,
  `log` text DEFAULT NULL,
  `reportId` int(11) NOT NULL,
  `json` mediumtext DEFAULT NULL,
  `reportversion` char(16) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPRESSED;

CREATE TABLE `vendorids` (
  `id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
CREATE TABLE `viewDeviceCount` (
`windows` bigint(21)
,`linux` bigint(21)
,`android` bigint(21)
);
CREATE TABLE `viewExtensions` (
`name` varchar(255)
,`coverage` bigint(21)
);
CREATE TABLE `viewExtensionsPlatforms` (
`name` varchar(255)
,`windows` bigint(21)
,`linux` bigint(21)
,`android` bigint(21)
,`features2` bigint(21)
,`properties2` bigint(21)
);
CREATE TABLE `viewFormatList` (
`value` int(11)
,`name` varchar(45)
);
CREATE TABLE `viewFormats` (
`name` varchar(45)
,`linear` bigint(21)
,`optimal` bigint(21)
,`buffer` bigint(21)
);
CREATE TABLE `viewReportCount` (
`windows` bigint(21)
,`linux` bigint(21)
,`android` bigint(21)
);
CREATE TABLE `viewSurfaceFormats` (
`formatname` char(255)
,`format` int(11)
,`coverage` bigint(21)
);
CREATE TABLE `viewSurfacePresentModes` (
`presentmode` int(11)
,`coverage` bigint(21)
);

CREATE TABLE `VkFormat` (
  `value` int(11) NOT NULL,
  `name` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `VkPhysicalDeviceType` (
  `value` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `VkPresentMode` (
  `value` int(11) NOT NULL,
  `name` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
DROP TABLE IF EXISTS `viewDeviceCount`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `viewDeviceCount`  AS SELECT (select count(distinct ifnull(`r`.`displayname`,`dp`.`devicename`)) from (`reports` `r` join `deviceproperties` `dp` on(`r`.`id` = `dp`.`reportid`)) where `r`.`ostype` = 0) AS `windows`, (select count(distinct ifnull(`r`.`displayname`,`dp`.`devicename`)) from (`reports` `r` join `deviceproperties` `dp` on(`r`.`id` = `dp`.`reportid`)) where `r`.`ostype` = 1) AS `linux`, (select count(distinct ifnull(`r`.`displayname`,`dp`.`devicename`)) from (`reports` `r` join `deviceproperties` `dp` on(`r`.`id` = `dp`.`reportid`)) where `r`.`ostype` = 2) AS `android` ;
DROP TABLE IF EXISTS `viewExtensions`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `viewExtensions`  AS SELECT `ext`.`name` AS `name`, (select count(distinct `r`.`id`) from (`reports` `r` join `deviceextensions` `de` on(`r`.`id` = `de`.`reportid`)) where `de`.`extensionid` = `ext`.`id`) AS `coverage` FROM `extensions` AS `ext` ;
DROP TABLE IF EXISTS `viewExtensionsPlatforms`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `viewExtensionsPlatforms`  AS SELECT `ext`.`name` AS `name`, (select count(distinct ifnull(`r`.`displayname`,`dp`.`devicename`)) from (`deviceproperties` `dp` join `reports` `r` on(`r`.`id` = `dp`.`reportid`)) where `r`.`ostype` = 0 and `r`.`id` in (select `de`.`reportid` from `deviceextensions` `de` where `de`.`extensionid` = `ext`.`id`)) AS `windows`, (select count(distinct ifnull(`r`.`displayname`,`dp`.`devicename`)) from (`deviceproperties` `dp` join `reports` `r` on(`r`.`id` = `dp`.`reportid`)) where `r`.`ostype` = 1 and `r`.`id` in (select `de`.`reportid` from `deviceextensions` `de` where `de`.`extensionid` = `ext`.`id`)) AS `linux`, (select count(distinct ifnull(`r`.`displayname`,`dp`.`devicename`)) from (`deviceproperties` `dp` join `reports` `r` on(`r`.`id` = `dp`.`reportid`)) where `r`.`ostype` = 2 and `r`.`id` in (select `de`.`reportid` from `deviceextensions` `de` where `de`.`extensionid` = `ext`.`id`)) AS `android`, (select count(distinct `df2`.`name`) from `devicefeatures2` `df2` where `df2`.`extension` = `ext`.`name`) AS `features2`, (select count(distinct `dp2`.`name`) from `deviceproperties2` `dp2` where `dp2`.`extension` = `ext`.`name`) AS `properties2` FROM `extensions` AS `ext` ;
DROP TABLE IF EXISTS `viewFormatList`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `viewFormatList`  AS SELECT `VkFormat`.`value` AS `value`, `VkFormat`.`name` AS `name` FROM `VkFormat` ;
DROP TABLE IF EXISTS `viewFormats`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `viewFormats`  AS SELECT `vf`.`name` AS `name`, (select count(distinct `df`.`reportid`) from `deviceformats` `df` where `df`.`formatid` = `vf`.`value` and `df`.`lineartilingfeatures` > 0) AS `linear`, (select count(distinct `df`.`reportid`) from `deviceformats` `df` where `df`.`formatid` = `vf`.`value` and `df`.`optimaltilingfeatures` > 0) AS `optimal`, (select count(distinct `df`.`reportid`) from `deviceformats` `df` where `df`.`formatid` = `vf`.`value` and `df`.`bufferfeatures` > 0) AS `buffer` FROM `VkFormat` AS `vf` ;
DROP TABLE IF EXISTS `viewReportCount`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `viewReportCount`  AS SELECT (select count(0) from `reports` where `reports`.`ostype` = 0) AS `windows`, (select count(0) from `reports` where `reports`.`ostype` = 1) AS `linux`, (select count(0) from `reports` where `reports`.`ostype` = 2) AS `android` ;
DROP TABLE IF EXISTS `viewSurfaceFormats`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `viewSurfaceFormats`  AS SELECT DISTINCT `VKFORMAT`(`df`.`format`) AS `formatname`, `df`.`format` AS `format`, (select count(distinct `dfs`.`reportid`) from `devicesurfaceformats` `dfs` where `dfs`.`format` = `df`.`format`) AS `coverage` FROM `devicesurfaceformats` AS `df` ;
DROP TABLE IF EXISTS `viewSurfacePresentModes`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `viewSurfacePresentModes`  AS SELECT DISTINCT `dp`.`presentmode` AS `presentmode`, (select count(distinct `dfp`.`reportid`) from `devicesurfacemodes` `dfp` where `dfp`.`presentmode` = `dp`.`presentmode`) AS `coverage` FROM `devicesurfacemodes` AS `dp` ;


ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`id`,`devicename`);

ALTER TABLE `devicealiases`
  ADD PRIMARY KEY (`devicename`),
  ADD KEY `alias` (`alias`);

ALTER TABLE `deviceextensions`
  ADD PRIMARY KEY (`reportid`,`extensionid`),
  ADD KEY `deviceextensions_extensionid_IDX` (`extensionid`) USING BTREE,
  ADD KEY `deviceextensions_reportid_IDX` (`reportid`) USING BTREE;

ALTER TABLE `devicefeatures`
  ADD PRIMARY KEY (`reportid`);

ALTER TABLE `devicefeatures2`
  ADD PRIMARY KEY (`reportid`,`id`),
  ADD KEY `index2` (`extension`),
  ADD KEY `ext_name_supported` (`name`,`extension`,`supported`);

ALTER TABLE `devicefeatures11`
  ADD PRIMARY KEY (`reportid`);

ALTER TABLE `devicefeatures12`
  ADD PRIMARY KEY (`reportid`);

ALTER TABLE `devicefeatures13`
  ADD PRIMARY KEY (`reportid`);

ALTER TABLE `deviceformats`
  ADD PRIMARY KEY (`reportid`,`formatid`),
  ADD KEY `index_id_linear` (`formatid`,`lineartilingfeatures`),
  ADD KEY `linear_tiling_format` (`lineartilingfeatures`),
  ADD KEY `deviceformats_formatid_IDX` (`formatid`) USING BTREE;

ALTER TABLE `deviceinstanceextensions`
  ADD PRIMARY KEY (`reportid`,`extensionid`),
  ADD KEY `index_extensionid` (`extensionid`);

ALTER TABLE `deviceinstancelayerextensions`
  ADD PRIMARY KEY (`reportid`,`devicelayerid`,`name`);

ALTER TABLE `deviceinstancelayers`
  ADD PRIMARY KEY (`reportid`,`layerid`);

ALTER TABLE `devicelayerextensions`
  ADD PRIMARY KEY (`reportid`,`devicelayerid`,`name`);

ALTER TABLE `devicelayers`
  ADD PRIMARY KEY (`reportid`,`layerid`);

ALTER TABLE `devicelimits`
  ADD PRIMARY KEY (`reportid`),
  ADD KEY `reportid` (`reportid`);

ALTER TABLE `devicememoryheaps`
  ADD PRIMARY KEY (`id`,`reportid`);

ALTER TABLE `devicememorytypes`
  ADD PRIMARY KEY (`id`,`reportid`);

ALTER TABLE `deviceplatformdetails`
  ADD PRIMARY KEY (`reportid`,`platformdetailid`);

ALTER TABLE `deviceprofiles`
  ADD PRIMARY KEY (`reportid`,`profileid`),
  ADD KEY `profile_idx` (`profileid`);

ALTER TABLE `deviceproperties`
  ADD PRIMARY KEY (`reportid`),
  ADD KEY `index_apidriverversion` (`apiversionraw`,`driverversionraw`),
  ADD KEY `index_devicename` (`devicename`),
  ADD KEY `deviceproperties_driverversion_IDX` (`driverversion`(255)) USING BTREE;

ALTER TABLE `deviceproperties2`
  ADD PRIMARY KEY (`reportid`,`id`),
  ADD KEY `index2` (`extension`);

ALTER TABLE `deviceproperties11`
  ADD PRIMARY KEY (`reportid`);

ALTER TABLE `deviceproperties12`
  ADD PRIMARY KEY (`reportid`);

ALTER TABLE `deviceproperties13`
  ADD PRIMARY KEY (`reportid`);

ALTER TABLE `devicequeues`
  ADD PRIMARY KEY (`reportid`,`id`);

ALTER TABLE `devicesurfacecapabilities`
  ADD PRIMARY KEY (`reportid`);

ALTER TABLE `devicesurfaceformats`
  ADD PRIMARY KEY (`id`,`reportid`),
  ADD KEY `index_format` (`format`);

ALTER TABLE `devicesurfacemodes`
  ADD PRIMARY KEY (`id`,`reportid`),
  ADD KEY `presentmode` (`presentmode`);

ALTER TABLE `extensions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `index_name` (`name`);

ALTER TABLE `instanceextensions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

ALTER TABLE `instancelayers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

ALTER TABLE `layerextensions`
  ADD PRIMARY KEY (`id`,`layerid`);

ALTER TABLE `layers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

ALTER TABLE `limitrequirements`
  ADD PRIMARY KEY (`limitname`,`feature`),
  ADD KEY `index2` (`limitname`);

ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `log_page_IDX` (`page`) USING BTREE;

ALTER TABLE `platformdetails`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`,`devicename`,`driverversion`),
  ADD KEY `index_version` (`version`),
  ADD KEY `index_devicename` (`devicename`),
  ADD KEY `index_osname` (`osname`),
  ADD KEY `index_ostype` (`ostype`),
  ADD KEY `index_internalid` (`internalid`),
  ADD KEY `index_apiversion` (`apiversion`),
  ADD KEY `index_displayname` (`displayname`),
  ADD KEY `reports_submissiondate_IDX` (`submissiondate`) USING BTREE,
  ADD KEY `reports_id_IDX` (`id`) USING BTREE;

ALTER TABLE `reportsjson`
  ADD PRIMARY KEY (`reportid`),
  ADD KEY `reportid` (`reportid`);

ALTER TABLE `reportupdatehistory`
  ADD PRIMARY KEY (`id`,`reportId`);

ALTER TABLE `vendorids`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `VkFormat`
  ADD PRIMARY KEY (`value`),
  ADD UNIQUE KEY `value_UNIQUE` (`value`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`),
  ADD KEY `index_name` (`name`),
  ADD KEY `index_value` (`value`);

ALTER TABLE `VkPhysicalDeviceType`
  ADD PRIMARY KEY (`value`);

ALTER TABLE `VkPresentMode`
  ADD PRIMARY KEY (`value`),
  ADD KEY `index_name` (`name`);


ALTER TABLE `blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `devicefeatures2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `devicememoryheaps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `devicememorytypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `deviceproperties2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `devicesurfaceformats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `devicesurfacemodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `extensions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `instanceextensions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `instancelayers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `layerextensions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `layers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `platformdetails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `reportupdatehistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `devicefeatures13`
  ADD CONSTRAINT `devicefeatures13_report` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `deviceformats`
  ADD CONSTRAINT `df_report` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `deviceproperties13`
  ADD CONSTRAINT `deviceproperties13_report` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `devicequeues`
  ADD CONSTRAINT `fk_qf_rep` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

