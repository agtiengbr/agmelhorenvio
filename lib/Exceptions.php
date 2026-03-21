<?php
class AgMelhorEnvioCommunicatorException extends Exception{}
class AgMelhorEnvioCommunicatorResponseCodeException extends Exception{}
class AgMelhorEnvioCommunicatorInvalidResponseBody extends Exception{}
class AgMelhorEnvioMissingArgumentsException extends Exception{}

class AgMelhorEnvioServiceFindingException extends Exception{};
class AgMelhorEnvioServiceSavingException extends Exception{};
class AgMelhorEnvioServiceCarrierSavingException extends Exception{};
class AgMelhorEnvioServiceImageCopyingException extends Exception{};

class AgMelhorEnvioOptionSavingException extends Exception{};
class AgMelhorEnvioOptionFindingException extends Exception{};

class AgMelhorEnvioServiceRequirementSavingException extends Exception{};
class AgMelhorEnvioServiceRequirementDeletingException extends Exception{};
class AgMelhorEnvioServiceRequirementFindingException extends Exception{};

class AgMelhorEnvioServiceOptionalSavingException extends Exception{};
class AgMelhorEnvioServiceOptionalDeletingException extends Exception{};
class AgMelhorEnvioServiceOptionalFindingException extends Exception{};


class AgMelhorEnvioPackageDatabaseException extends Exception{};

class AgMelhorEnvioUnauthenticatedException extends Exception{};