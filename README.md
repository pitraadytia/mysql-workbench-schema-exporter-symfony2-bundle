forked from Easen/mysql-workbench-schema-exporter-bundle

README
======


Setup
-----

Workbench files should be saved in the Resources/workbench/*.mwb directory inner the bundle that is in the configuration. This is configurable per schema.


Configuration
=============

Single schema
-------------

`schema_name` here refers to name of the Workbench file

    mysql_workbench_schema_exporter:
        schema:
            schema_name:
                bundle: YourBundle


Multiple schemas
----------------

`schema_name` here refers to name of the Workbench file

    mysql_workbench_schema_exporter:
        schema:
            schema1_name:
                bundle: YourBundle
            schema2_name:
                bundle: YourBundle
            schema3_name:
                bundle: YourBundle
                params:
                    repositoryNamespace: "Acme\\SomeBundle\\Entity\\Repository"
                    backupExistingFile: true,
                    skipPluralNameChecking: false,
                    enhanceManyToManyDetection: true,
                    bundleNamespace: "",
                    entityNamespace: "",
                    repositoryNamespace: "",
                    useAnnotationPrefix: "ORM\\",
                    useAutomaticRepository: true,
                    indentation: 4,
                    filename: "%entity%.%extension%",
                    quoteIdentifier: false


Sample

mysql_workbench_schema_exporter:
    schema:
       vending:
         bundle: SandboxGeneratedBundle
         params:
             indentation: 4
             useTabs: false
             skipPluralNameChecking: false
             backupExistingFile: false
             enhanceManyToManyDetection: true
             logToConsole: ~
             logFile: ~
             useAnnotationPrefix: "ORM\\"
             repositoryNamespace: "Sandbox\\GeneratedBundle\\Entity\\Repository"
             useAutomaticRepository: true
             entityNamespace: "Entity\\Model"
             skipGetterAndSetter: false
             quoteIdentifier: false
             baseNamespace: "VN"
             generateEntitySerialization: false
             generateEntityToArray: true
             bundleNamespaceTo: 'VN\\CoreBundle'

Execution
=========

To process the files execute the command in the terminal:

	app/console mysqlworkbenchschemaexporter:dump

