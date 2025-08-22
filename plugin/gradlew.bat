@rem ---------------------------------------------------------------------------
@rem Gradle startup script for Windows
@rem Minimal, standard script suitable for committing to VCS.
@rem ---------------------------------------------------------------------------

@if "%DEBUG%" == "" @echo off
setlocal

set DIRNAME=%~dp0
if "%DIRNAME%" == "" set DIRNAME=.
set APP_BASE_NAME=%~n0
set APP_HOME=%DIRNAME%

set DEFAULT_JVM_OPTS=-Xms64m -Xmx1g

set CLASSPATH=%APP_HOME%\gradle\wrapper\gradle-wrapper.jar

set JAVA_EXE=java.exe
if defined JAVA_HOME (
    set JAVA_EXE=%JAVA_HOME%\bin\java.exe
)

if exist "%JAVA_EXE%" goto init

echo ERROR: JAVA_HOME is not set and no 'java' command could be found in your PATH.
exit /b 1

:init
set GRADLE_MAIN_CLASS=org.gradle.wrapper.GradleWrapperMain

"%JAVA_EXE%" %DEFAULT_JVM_OPTS% %JAVA_OPTS% %GRADLE_OPTS% -classpath "%CLASSPATH%" %GRADLE_MAIN_CLASS% %*

endlocal
