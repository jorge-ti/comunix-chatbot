#!/home/extend/python-3-8-10/bin/python3.8

import os
import sys
import time

from google.cloud import speech
from google.cloud import storage
from google.api_core import page_iterator
from wrapt_timeout_decorator import *



os.environ['GOOGLE_APPLICATION_CREDENTIALS'] = '/root/google-cloud-sdk/key.json'
os.system("ln -s /usr/lib/x86_64-linux-gnu/libffi.so.6 /usr/lib/x86_64-linux-gnu/libffi.so.5")


def listar_buckets():
    # Instantiates a client
    storage_client = storage.Client()

    # List all the buckets available
    for bucket in storage_client.list_buckets():
        print(bucket)


def upload_file(arquivo):
    storage_client = storage.Client()

    #buckets = list(storage_client.list_buckets())

    bucket = storage_client.get_bucket("sophia-contax", timeout=5)
    
    arquivo_nome = arquivo.split("/")[-1]

    blob = bucket.blob(arquivo_nome)
    blob.upload_from_filename(arquivo, timeout=5)


def traduz_audio(arquivo):
    # Instantiates a client
    client = speech.SpeechClient()

    # The name of the audio file to transcribe

    arquivo_nome = arquivo.split("/")[-1]
    audio = speech.RecognitionAudio(uri="gs://sophia-contax/" + arquivo_nome)

    config = speech.RecognitionConfig(
        encoding=speech.RecognitionConfig.AudioEncoding.LINEAR16,
        sample_rate_hertz=48000,
        language_code="pt-br",
    )

    # Detects speech in the audio file
    response = client.recognize(config=config, audio=audio, timeout=5)

    for result in response.results:
        retorno = result.alternatives[0].transcript
    return retorno


#@timeout(10)
def google_stt(arquivo):
    try:
        arquivo = arquivo.strip()
        upload_file(arquivo)
        texto = traduz_audio(arquivo)
    except:
        texto = '0'

    print(texto)
    return texto


google_stt(sys.argv[1])


